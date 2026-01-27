<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TryoutManagerController extends Controller
{
    /**
     * Menampilkan daftar tryout di halaman admin.
     */
    public function index()
    {
        $tryouts = Tryout::orderBy('created_at', 'desc')->get();

        return Inertia::render('Admin/Tryout/Index', [
            'tryouts' => $tryouts
        ]);
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'duration_minutes' => 'required|integer|min:1',
        'is_paid' => 'required|boolean',
        'price' => 'required_if:is_paid,true|numeric|min:0',
        // Fitur Baru
        'is_published' => 'required|boolean',
        'published_at' => 'nullable|date',
        'started_at' => 'nullable|date',
    ]);

    Tryout::create($validated);
    return redirect()->back()->with('success', 'Paket berhasil dibuat.');
}

public function update(Request $request, Tryout $tryout)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'duration_minutes' => 'required|integer|min:1',
        'is_paid' => 'required|boolean',
        'price' => 'required_if:is_paid,true|numeric|min:0',
        // Fitur Baru
        'is_published' => 'required|boolean',
        'published_at' => 'nullable|date',
        'started_at' => 'nullable|date',
    ]);

    $tryout->update($validated);
    return redirect()->back()->with('success', 'Paket berhasil diperbarui.');
}

    /**
     * Menghapus paket tryout.
     */
    public function destroy(Tryout $tryout)
    {
        $tryout->delete();

        return redirect()->back()->with('success', 'Paket Tryout berhasil dihapus.');
    }
}