<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TryoutManagerController extends Controller
{
    public function index(Request $request)
    {
        $query = Tryout::query()->withCount('questions');

        // Filter tipe (bukan adidaya/akbar)
        $query->where(function ($q) {
            $q->whereNotIn('type', ['adidaya', 'akbar'])
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

    public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'duration' => 'required|integer|min:1',
        'description' => 'nullable|string',
        'is_paid' => 'required|boolean',
        'price' => 'required_if:is_paid,true|numeric|min:0',
        'is_published' => 'boolean',
        'end_date' => 'nullable|date', // <--- Validasi Tanggal
    ]);

    $validated['type'] = 'general'; // Default tipe

    Tryout::create($validated);

    return back()->with('message', 'Paket Tryout berhasil ditambahkan!');
}

public function update(Request $request, Tryout $tryout)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'duration' => 'required|integer|min:1',
        'description' => 'nullable|string',
        'is_paid' => 'required|boolean',
        'price' => 'required_if:is_paid,true|numeric|min:0',
        'is_published' => 'boolean',
        'end_date' => 'nullable|date', // <--- Validasi Tanggal
    ]);

    $tryout->update($validated);

    return back()->with('message', 'Paket Tryout berhasil diperbarui!');
}

    public function destroy(Tryout $tryout)
    {
        $tryout->delete();
        return back()->with('message', 'Paket Tryout berhasil dihapus.');
    }
}