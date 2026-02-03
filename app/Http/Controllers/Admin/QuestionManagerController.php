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
        $questions = $tryout->questions()->orderBy('order', 'asc')->get();
        return Inertia::render('Admin/Question/Index', [
            'tryout' => $tryout,
            'questions' => $questions
        ]);
    }

    public function store(Request $request, Tryout $tryout)
    {
        $request->validate([
            'type' => 'required|in:TWK,TIU,TKP',
            'content' => 'required', // Kembali menggunakan 'content'
            'image' => 'nullable|image|max:2048', 
            'options' => 'required|array',
            'explanation' => 'nullable|string',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('questions', 'public');
        }

        $tryout->questions()->create($data);

        return back()->with('message', 'Soal berhasil disimpan!');
    }

    public function update(Request $request, Tryout $tryout, Question $question)
    {
        $request->validate([
            'type' => 'required|in:TWK,TIU,TKP',
            'content' => 'required', // Kembali menggunakan 'content'
            'image' => 'nullable|image|max:2048',
            'options' => 'required|array',
            'explanation' => 'nullable|string',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            if ($question->image) {
                Storage::disk('public')->delete($question->image);
            }
            $data['image'] = $request->file('image')->store('questions', 'public');
        }

        $question->update($data);

        return back()->with('message', 'Soal berhasil diperbarui!');
    }

    public function destroy(Tryout $tryout, Question $question)
    {
        if ($question->image) {
            Storage::disk('public')->delete($question->image);
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
        'file' => 'required|file|mimes:csv,txt|max:2048', // Gunakan CSV
    ]);

    $file = $request->file('file');
    $path = $file->getRealPath();

    // Buka file untuk mendeteksi delimiter (koma atau titik koma)
    $handle = fopen($path, "r");
    $firstLine = fgets($handle);
    $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
    rewind($handle); // Kembalikan pointer ke awal file

    fgetcsv($handle, 1000, $delimiter); // Lewati header baris pertama

    DB::beginTransaction();
    try {
        $count = 0;
        while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
            // Lewati jika kolom inti (tipe dan soal) kosong
            if (empty($row[0]) || empty($row[1])) continue;

            $type = strtoupper(trim($row[0]));
            
            // Format Pilihan A-E
            $options = [
                'a' => $row[2] ?? '',
                'b' => $row[3] ?? '',
                'c' => $row[4] ?? '',
                'd' => $row[5] ?? '',
                'e' => $row[6] ?? '',
            ];

            $correctAnswer = trim($row[7] ?? '');
            $tkpScores = null;

            // Logika khusus Skor TKP (format 54321)
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

            // Simpan ke database dengan kolom 'content'
            $tryout->questions()->create([
                'type'           => $type,
                'content'        => $row[1],
                'options'        => $options,
                'correct_answer' => $correctAnswer,
                'tkp_scores'     => $tkpScores,
                'explanation'    => $row[8] ?? '',
                'order'          => 999
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