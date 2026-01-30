<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TryoutAkbarController extends Controller
{
    /**
     * Menampilkan daftar Tryout Akbar.
     */
    public function index(Request $request)
    {
        $query = Tryout::query()->where('type', 'akbar');

        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $tryouts = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Admin/TryoutAkbar/Index', [
            'tryouts' => $tryouts,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Halaman Verifikasi Peserta
     */
    public function participants(Request $request, Tryout $tryout) // Note: Model binding 'tryout'
    {
        // Pastikan ini tryout akbar
        if($tryout->type !== 'akbar') abort(404);

        $query = \App\Models\Transaction::with('user')
            ->where('tryout_id', $tryout->id);

        // Filter Status (Default: Pending)
        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            // Jika tidak ada filter, prioritaskan pending di atas
            $query->orderByRaw("FIELD(status, 'pending', 'paid', 'failed')");
        }

        $participants = $query->latest()->paginate(20)->withQueryString();

        // Hitung statistik kecil
        $stats = [
            'total' => \App\Models\Transaction::where('tryout_id', $tryout->id)->count(),
            'pending' => \App\Models\Transaction::where('tryout_id', $tryout->id)->where('status', 'pending')->count(),
            'paid' => \App\Models\Transaction::where('tryout_id', $tryout->id)->where('status', 'paid')->count(),
        ];

        return Inertia::render('Admin/TryoutAkbar/Verify', [
            'tryout' => $tryout,
            'participants' => $participants,
            'stats' => $stats,
            'filters' => $request->only(['status'])
        ]);
    }

    /**
     * Proses Approve / Reject
     */
    public function verifyRegistration(Request $request, \App\Models\Transaction $transaction)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'reason' => 'nullable|string' // Opsional jika reject kasih alasan
        ]);

        if ($request->action === 'approve') {
            $transaction->update(['status' => 'paid']); // Paid = Valid
            $message = 'Peserta berhasil diverifikasi (Diterima).';
        } else {
            $transaction->update(['status' => 'failed']);
            $message = 'Peserta ditolak.';
        }

        return back()->with('success', $message);
    }

    /**
     * Menampilkan form pembuatan Tryout Akbar baru.
     */
    public function create()
    {
        return Inertia::render('Admin/TryoutAkbar/Create');
    }

    /**
     * Menyimpan Tryout Akbar baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'started_at' => 'required|date',
            'ended_at' => 'required|date|after:started_at',
            'duration' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string', // Kolom khusus Akbar
            'is_published' => 'boolean',
        ]);

        // Paksa type menjadi 'akbar'
        $validated['type'] = 'akbar';
        
        // Default published false jika tidak ada request
        $validated['is_published'] = $request->boolean('is_published', false);

        Tryout::create($validated);

        return redirect()->route('admin.tryout-akbar.index')
            ->with('success', 'Event Tryout Akbar berhasil dibuat.');
    }

    /**
     * Menampilkan form edit.
     * Note: Parameter di route resource biasanya 'tryout_akbar', kita bind ke model Tryout.
     */
    public function edit($id)
    {
        $tryout = Tryout::where('id', $id)->where('type', 'akbar')->firstOrFail();

        return Inertia::render('Admin/TryoutAkbar/Edit', [
            'tryout' => $tryout
        ]);
    }

    /**
     * Update data Tryout Akbar.
     */
    public function update(Request $request, $id)
    {
        $tryout = Tryout::where('id', $id)->where('type', 'akbar')->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'started_at' => 'required|date',
            'ended_at' => 'required|date|after:started_at',
            'duration' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'is_published' => 'boolean',
        ]);

        $tryout->update($validated);

        return redirect()->route('admin.tryout-akbar.index')
            ->with('success', 'Event Tryout Akbar berhasil diperbarui.');
    }

    /**
     * Hapus Tryout Akbar.
     */
    public function destroy($id)
    {
        $tryout = Tryout::where('id', $id)->where('type', 'akbar')->firstOrFail();
        
        // Opsional: Cek jika sudah ada transaksi, cegah hapus
        if ($tryout->transactions()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus event yang sudah memiliki peserta.');
        }

        $tryout->delete();

        return back()->with('success', 'Event Tryout Akbar berhasil dihapus.');
    }
}