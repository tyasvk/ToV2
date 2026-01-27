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
    // Update juga fungsi index agar soal diurutkan berdasarkan 'order'
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
        'image' => 'nullable|image|max:2048', // Maks 2MB
        'options' => 'required|array',
        'explanation' => 'nullable|string',
    ]);

    $data = $request->all();

    // Handle Upload Gambar
    if ($request->hasFile('image')) {
        $data['image'] = $request->file('image')->store('questions', 'public');
    }

    $tryout->questions()->create($data);

    return back()->with('message', 'Soal berhasil disimpan!');
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

    // Jika ada upload gambar baru
    if ($request->hasFile('image')) {
        // Hapus gambar lama jika ada
        if ($question->image) {
            Storage::disk('public')->delete($question->image);
        }
        // Simpan gambar baru
        $data['image'] = $request->file('image')->store('questions', 'public');
    }

    $question->update($data);

    return back()->with('message', 'Soal berhasil diperbarui!');
}

    public function reorder(Request $request)
{
    $ids = $request->ids; // Array ID soal sesuai urutan baru
    
    // Update urutan secara massal untuk efisiensi
    foreach ($ids as $index => $id) {
        DB::table('questions')->where('id', $id)->update(['order' => $index]);
    }

    return back()->with('message', 'Urutan soal berhasil diperbarui!');
}
}