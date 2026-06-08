<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Tryout;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QuestionManagerController extends Controller
{
    public function index(Tryout $tryout)
    {
        // Data diambil mentah (termasuk tag HTML gambar) lalu dikirim ke Vue
        $questions = $tryout->questions()->orderBy('order', 'asc')->get();
        
        return Inertia::render('Admin/Question/Index', [
            'tryout'    => $tryout,
            'questions' => $questions
        ]);
    }

    public function store(Request $request, Tryout $tryout)
    {
        // Validasi: Wajib isi teks JIKA gambar kosong, wajib isi gambar JIKA teks kosong
        $request->validate([
            'type'            => 'required|in:TWK,TIU,TKP',
            'content'         => 'required_without:image|nullable|string',
            'image'           => 'required_without:content|nullable|image|max:2048',
            'options'         => 'required|array',
            'option_images.*' => 'nullable|image|max:2048', 
            'explanation'     => 'nullable|string',
            'correct_answer'  => 'nullable|string',
            'tkp_scores'      => 'nullable|array',
        ], [
            'content.required_without' => 'Teks pertanyaan harus diisi jika tidak ada gambar.',
            'image.required_without'   => 'Gambar harus diunggah jika teks pertanyaan kosong.',
        ]);

        $data = $request->except(['option_images', 'existing_option_images']);
        
        // Mencegah error database jika kolom 'content' tidak boleh NULL di DB
        $data['content'] = $request->content ?? '';

        // Handle Gambar Soal (Pertanyaan Utama)
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('questions', 'public');
        }

        // Handle Gambar Opsi (Khusus TIU)
        if ($request->type === 'TIU' && $request->hasFile('option_images')) {
            $optionImagePaths = [];
            foreach (['a', 'b', 'c', 'd', 'e'] as $key) {
                if ($request->hasFile("option_images.{$key}")) {
                    $optionImagePaths[$key] = $request->file("option_images.{$key}")->store('option_images', 'public');
                }
            }
            $data['option_images'] = $optionImagePaths;
        }

        // Set Order otomatis di akhir
        $maxOrder = $tryout->questions()->max('order') ?? 0;
        $data['order'] = $maxOrder + 1;

        $tryout->questions()->create($data);

        return back()->with('message', 'Soal berhasil disimpan!');
    }

    public function update(Request $request, Tryout $tryout, Question $question)
    {
        $request->validate([
            'type'            => 'required|in:TWK,TIU,TKP',
            'content'         => 'required_without:image|nullable|string',
            'image'           => 'required_without:content|nullable|image|max:2048',
            'options'         => 'required|array',
            'option_images.*' => 'nullable|image|max:2048',
            'explanation'     => 'nullable|string',
            'correct_answer'  => 'nullable|string',
            'tkp_scores'      => 'nullable|array',
        ], [
            'content.required_without' => 'Teks pertanyaan harus diisi jika tidak ada gambar.',
            'image.required_without'   => 'Gambar harus diunggah jika teks pertanyaan kosong.',
        ]);

        $data = $request->except(['option_images', 'existing_option_images']);
        $data['content'] = $request->content ?? '';

        // Handle update Gambar Soal Utama
        if ($request->hasFile('image')) {
            if ($question->image && Storage::disk('public')->exists($question->image)) {
                Storage::disk('public')->delete($question->image);
            }
            $data['image'] = $request->file('image')->store('questions', 'public');
        }

        // Handle update Gambar Opsi (Khusus TIU)
        $optionImagePaths = is_string($question->option_images) ? json_decode($question->option_images, true) : ($question->option_images ?? []);

        if ($request->type === 'TIU') {
            foreach (['a', 'b', 'c', 'd', 'e'] as $key) {
                if ($request->hasFile("option_images.{$key}")) {
                    // Hapus gambar opsi lama jika ada
                    if (!empty($optionImagePaths[$key]) && Storage::disk('public')->exists($optionImagePaths[$key])) {
                        Storage::disk('public')->delete($optionImagePaths[$key]);
                    }
                    // Simpan gambar opsi baru
                    $optionImagePaths[$key] = $request->file("option_images.{$key}")->store('option_images', 'public');
                }
            }
            $data['option_images'] = $optionImagePaths;
        }

        $question->update($data);

        return back()->with('message', 'Soal berhasil diperbarui!');
    }

    public function destroy(Tryout $tryout, Question $question)
    {
        // Hapus file Gambar Soal Utama dari Storage
        if ($question->image && Storage::disk('public')->exists($question->image)) {
            Storage::disk('public')->delete($question->image);
        }

        // Hapus file Gambar Opsi dari Storage
        $optionImages = is_string($question->option_images) ? json_decode($question->option_images, true) : ($question->option_images ?? []);
        if (is_array($optionImages)) {
            foreach ($optionImages as $imgPath) {
                if (!empty($imgPath) && Storage::disk('public')->exists($imgPath)) {
                    Storage::disk('public')->delete($imgPath);
                }
            }
        }

        $question->delete();
        return back()->with('message', 'Soal berhasil dihapus.');
    }

    public function reorder(Request $request)
    {
        $ids = $request->ids;
        foreach ($ids as $index => $id) {
            Question::where('id', $id)->update(['order' => $index + 1]);
        }
        return back()->with('message', 'Urutan soal diperbarui');
    }

    public function import(Request $request, Tryout $tryout)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        $handle = fopen($path, "r");
        $firstLine = fgets($handle);
        $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
        rewind($handle);

        fgetcsv($handle, 1000, $delimiter); // Lewati header

        DB::beginTransaction();
        try {
            $count = 0;
            $maxOrder = $tryout->questions()->max('order') ?? 0;

            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                if (empty($row[0]) || empty($row[1])) continue;

                $type = strtoupper(trim($row[0]));
                
                $options = [
                    'a' => $row[2] ?? '',
                    'b' => $row[3] ?? '',
                    'c' => $row[4] ?? '',
                    'd' => $row[5] ?? '',
                    'e' => $row[6] ?? '',
                ];

                $correctAnswer = trim($row[7] ?? '');
                $tkpScores = null;

                if ($type === 'TKP' && strlen($correctAnswer) === 5) {
                    $tkpScores = [
                        'a' => (int)$correctAnswer[0],
                        'b' => (int)$correctAnswer[1],
                        'c' => (int)$correctAnswer[2],
                        'd' => (int)$correctAnswer[3],
                        'e' => (int)$correctAnswer[4],
                    ];
                    $correctAnswer = null;
                }

                $maxOrder++;

                $tryout->questions()->create([
                    'type'           => $type,
                    'content'        => $row[1],
                    'options'        => $options,
                    'correct_answer' => $correctAnswer,
                    'tkp_scores'     => $tkpScores,
                    'explanation'    => $row[8] ?? '',
                    'order'          => $maxOrder
                ]);
                $count++;
            }

            DB::commit();
            fclose($handle);
            return back()->with('message', "Berhasil mengimpor $count soal!");

        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return back()->withErrors(['file' => 'Gagal membaca isi file: ' . $e->getMessage()]);
        }
    }
}