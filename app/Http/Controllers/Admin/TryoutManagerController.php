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
        $query = Tryout::query()->withCount('questions');

        $query->where(function ($q) {
            $q->where('type', '!=', 'akbar')
              ->orWhereNull('type');
        });

        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

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
        // 1. Validasi bisa menerima 'duration' atau 'duration_minutes'
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'duration' => 'sometimes|integer|min:1',         // Nama kolom baru
            'duration_minutes' => 'sometimes|integer|min:1', // Nama input lama (jaga-jaga)
            'description' => 'nullable|string',
            'is_paid' => 'required|boolean', 
            'price' => 'required_if:is_paid,true|numeric|min:0',
        ]);

        // 2. Normalisasi: Pastikan data masuk ke kolom 'duration'
        if (isset($validated['duration_minutes'])) {
            $validated['duration'] = $validated['duration_minutes'];
            unset($validated['duration_minutes']); // Hapus agar tidak error di DB
        }

        // Default value jika tidak ada input durasi sama sekali
        if (!isset($validated['duration'])) {
            $validated['duration'] = 110; 
        }

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
            'duration' => 'sometimes|integer|min:1',
            'duration_minutes' => 'sometimes|integer|min:1',
            'description' => 'nullable|string',
            'published_at' => 'nullable|date',
            'started_at' => 'nullable|date',
            'is_paid' => 'required|boolean',
            'price' => 'required_if:is_paid,true|numeric|min:0',
        ]);

        // 1. Normalisasi: Mapping duration_minutes ke duration
        if (isset($validated['duration_minutes'])) {
            $validated['duration'] = $validated['duration_minutes'];
            unset($validated['duration_minutes']); // PENTING: Hapus key ini!
        }

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