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

    public function verifyRegistration(Request $request, \App\Models\Transaction $transaction)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'reason' => 'nullable|string|max:255' // Validasi input alasan
        ]);

        if ($request->action === 'approve') {
            $transaction->update([
                'status' => 'paid',
                'rejection_note' => null // Hapus catatan jika akhirnya diterima
            ]);
            $message = 'Peserta berhasil diverifikasi (Diterima).';
        } else {
            $transaction->update([
                'status' => 'failed',
                'rejection_note' => $request->reason ?? 'Bukti tidak valid.' // Simpan alasan
            ]);
            $message = 'Peserta ditolak.';
        }

        return back()->with('success', $message);
    }

    /**
     * Halaman Verifikasi Peserta
     */
public function participants(Request $request, Tryout $tryout)
    {
        if($tryout->type !== 'akbar') abort(404);

        // LOGIC BARU: Ambil hanya ID transaksi TERAKHIR untuk setiap user di tryout ini
        // Ini memastikan "Satu Orang Satu Data" (data terbaru yang muncul)
        $latestTransactionIds = \App\Models\Transaction::where('tryout_id', $tryout->id)
            ->selectRaw('MAX(id) as id')
            ->groupBy('user_id')
            ->pluck('id');

        $query = \App\Models\Transaction::whereIn('id', $latestTransactionIds)
            ->with('user');

        // Filter Status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        } else {
            // Sorting: Pending paling atas, lalu Failed, lalu Paid
            $query->orderByRaw("FIELD(status, 'pending', 'failed', 'paid')");
        }

        // Search (Opsional, untuk mencari nama peserta)
        if ($request->has('search')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $participants = $query->paginate(20)->withQueryString();

        // Hitung statistik realtime
        // Kita hitung berdasarkan data unik juga agar akurat
        $baseStats = \App\Models\Transaction::whereIn('id', $latestTransactionIds);
        
        $stats = [
            'total' => (clone $baseStats)->count(),
            'pending' => (clone $baseStats)->where('status', 'pending')->count(),
            'paid' => (clone $baseStats)->where('status', 'paid')->count(),
        ];

        return Inertia::render('Admin/TryoutAkbar/Verify', [
            'tryout' => $tryout,
            'participants' => $participants,
            'stats' => $stats,
            'filters' => $request->only(['status', 'search'])
        ]);
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

        // Pastikan tipe boolean terbaca dengan benar
        $validated['is_published'] = $request->boolean('is_published');

        // Update data
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