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
        return Inertia::render('Admin/Question/Index', [
            'tryout' => $tryout,
            'questions' => $tryout->questions()->orderBy('order', 'asc')->get()
        ]);
    }

    public function store(Request $request, Tryout $tryout)
    {
        $request->validate([
            'type' => 'required|in:TWK,TIU,TKP',
            'content' => 'required',
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

    // --- FITUR IMPORT CSV ---
    public function import(Request $request, Tryout $tryout)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx|max:2048',
        ]);

        $file = $request->file('file');
        
        // Membuka file CSV
        if (($handle = fopen($file->getRealPath(), "r")) !== FALSE) {
            $header = fgetcsv($handle, 1000, ","); // Lewati baris header
            
            DB::beginTransaction();
            try {
                while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    // Pastikan urutan kolom di CSV:
                    // 0: type (TWK/TIU/TKP)
                    // 1: content (Soal)
                    // 2: option_a
                    // 3: option_b
                    // 4: option_c
                    // 5: option_d
                    // 6: option_e
                    // 7: correct_answer (kunci a/b/c/d/e) atau score JSON untuk TKP
                    // 8: explanation (pembahasan)

                    if (count($row) < 8) continue; // Skip jika data tidak lengkap

                    $type = strtoupper($row[0]);
                    
                    // Format Options
                    $options = [
                        'a' => $row[2],
                        'b' => $row[3],
                        'c' => $row[4],
                        'd' => $row[5],
                        'e' => $row[6],
                    ];

                    // Handle TKP Scores atau Kunci Jawaban Biasa
                    $correctAnswer = $row[7];
                    $tkpScores = null;

                    if ($type === 'TKP') {
                        // Jika TKP, kolom correct_answer diisi JSON string misal: {"a":5,"b":4...}
                        // Atau format manual "54321" (a=5, b=4 dst) - disini asumsi manual input score json valid
                        // Untuk simplicity import CSV, kita asumsikan user mengisi format: 54321
                        // Dimana urutan digit mewakili skor a,b,c,d,e
                        if (is_numeric($correctAnswer) && strlen($correctAnswer) == 5) {
                            $tkpScores = [
                                'a' => $correctAnswer[0],
                                'b' => $correctAnswer[1],
                                'c' => $correctAnswer[2],
                                'd' => $correctAnswer[3],
                                'e' => $correctAnswer[4],
                            ];
                            $correctAnswer = null; // TKP tidak punya satu kunci
                        }
                    }

                    $tryout->questions()->create([
                        'type' => $type,
                        'content' => $row[1],
                        'options' => $options,
                        'correct_answer' => $correctAnswer,
                        'tkp_scores' => $tkpScores,
                        'explanation' => isset($row[8]) ? $row[8] : '',
                        'order' => 999 // Taruh di paling bawah
                    ]);
                }
                DB::commit();
                return back()->with('message', 'Import soal berhasil!');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->withErrors(['file' => 'Gagal import: ' . $e->getMessage()]);
            }
            fclose($handle);
        }

        return back()->withErrors(['file' => 'Gagal membaca file CSV.']);
    }

    public function destroy(Question $question)
    {
        $question->delete();
        return back()->with('message', 'Soal berhasil dihapus.');
    }

    public function update(Request $request, Question $question)
    {
        $request->validate([
            'type' => 'required|in:TWK,TIU,TKP',
            'content' => 'required',
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

    public function reorder(Request $request)
    {
        $ids = $request->ids; 
        foreach ($ids as $index => $id) {
            DB::table('questions')->where('id', $id)->update(['order' => $index]);
        }
        return back()->with('message', 'Urutan soal berhasil diperbarui!');
    }
}