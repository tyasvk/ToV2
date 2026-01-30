<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TryoutManagerController extends Controller
{
    /**
     * Menampilkan daftar Tryout Reguler (Non-Akbar).
     */
    public function index(Request $request)
    {
        // Query dasar: Hitung jumlah soal
        $query = Tryout::query()->withCount('questions');

        // FILTER PENTING: Jangan tampilkan tipe 'akbar'
        $query->where(function ($q) {
            $q->where('type', '!=', 'akbar')
              ->orWhereNull('type');
        });

        // Fitur Pencarian
        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Ambil data dengan pagination (10 per halaman)
        $tryouts = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Admin/Tryout/Index', [
            'tryouts' => $tryouts,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Menyimpan tryout baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'is_paid' => 'required|boolean', 
            'price' => 'required_if:is_paid,true|numeric|min:0',
        ]);

        // SET DEFAULT TYPE: 'general' (Agar tidak dianggap akbar)
        $validated['type'] = 'general';

        Tryout::create($validated);

        return back()->with('message', 'Paket Tryout berhasil ditambahkan!');
    }

    /**
     * Update data tryout.
     */
    public function update(Request $request, Tryout $tryout)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'published_at' => 'nullable|date', // Diperbolehkan null
            'started_at' => 'nullable|date',   // Diperbolehkan null
            'is_paid' => 'required|boolean',
            'price' => 'required_if:is_paid,true|numeric|min:0',
        ]);

        $tryout->update($validated);

        return back()->with('message', 'Paket tryout berhasil diperbarui!');
    }

    /**
     * Hapus tryout.
     */
    public function destroy(Tryout $tryout)
    {
        $tryout->delete();
        return back()->with('message', 'Paket Tryout berhasil dihapus.');
    }

    /**
     * Mengubah status aktif/non-aktif.
     */
    public function toggleStatus(Tryout $tryout)
    {
        $tryout->update(['is_active' => !$tryout->is_active]);
        return back()->with('message', 'Status paket berhasil diperbarui.');
    }
}