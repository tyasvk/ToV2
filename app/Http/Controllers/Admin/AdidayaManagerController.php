<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdidayaManagerController extends Controller
{
    public function index(Request $request)
    {
        $query = Tryout::query()->withCount('questions')->where('type', 'adidaya');

        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $tryouts = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Admin/Adidaya/Index', [
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
            'published_at' => 'nullable|date',
            'started_at' => 'nullable|date',
        ]);

        $validated['type'] = 'adidaya';

        Tryout::create($validated);

        return back()->with('message', 'Paket Adidaya berhasil ditambahkan!');
    }

    /**
     * PERBAIKAN: Nama variabel harus sesuai dengan parameter rute {adidaya_manage}
     * Atau Anda bisa menggunakan Type Hinting (Tryout $adidayaManage)
     */
    public function update(Request $request, $id)
    {
        $tryout = Tryout::findOrFail($id); // Mencari data berdasarkan ID secara manual agar pasti ketemu

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'duration' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'is_paid' => 'required|boolean',
            'price' => 'required_if:is_paid,true|numeric|min:0',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
            'started_at' => 'nullable|date',
        ]);

        $tryout->update($validated);

        return back()->with('message', 'Paket Adidaya berhasil diperbarui!');
    }

    /**
     * PERBAIKAN: Samakan juga untuk method destroy
     */
    public function destroy($id)
    {
        $tryout = Tryout::findOrFail($id);
        $tryout->delete();
        
        return back()->with('message', 'Paket Adidaya berhasil dihapus.');
    }
}