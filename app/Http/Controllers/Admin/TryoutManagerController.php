<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TryoutManagerController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Tryout/Index', [
            // Mengambil semua paket, urutkan terbaru, dan hitung jumlah soalnya
            'tryouts' => Tryout::withCount('questions')->latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        Tryout::create($request->all());

        return back()->with('message', 'Paket Tryout berhasil ditambahkan!');
    }

    public function update(Request $request, Tryout $tryout)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'duration_minutes' => 'required|integer|min:1',
        'description' => 'nullable|string',
        'published_at' => 'required',
        'started_at' => 'required',
    ]);

    $tryout->update($request->all());

    return back()->with('message', 'Paket tryout berhasil diperbarui!');
}

    public function destroy(Tryout $tryout)
    {
        $tryout->delete();
        return back()->with('message', 'Paket Tryout berhasil dihapus.');
    }

    public function toggleStatus(Tryout $tryout)
{
    $tryout->update(['is_active' => !$tryout->is_active]);
    return back()->with('message', 'Status paket berhasil diperbarui.');
}
}