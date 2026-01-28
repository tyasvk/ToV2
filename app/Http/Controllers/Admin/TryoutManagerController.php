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
            'tryouts' => Tryout::withCount('questions')->latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'is_paid' => 'required|boolean', // Validasi status berbayar
            'price' => 'required_if:is_paid,true|numeric|min:0', // Harga wajib diisi jika berbayar
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
            'is_paid' => 'required|boolean', // Validasi status berbayar
            'price' => 'required_if:is_paid,true|numeric|min:0', // Harga wajib diisi jika berbayar
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