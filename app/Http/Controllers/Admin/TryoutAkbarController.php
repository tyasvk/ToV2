<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use App\Models\Transaction;
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
     * Menampilkan halaman detail/verifikasi peserta Tryout Akbar.
     * Logika ini menggantikan fungsi participants() sebelumnya.
     */
    public function show(Request $request, $id)
    {
        $tryout = Tryout::findOrFail($id);

        if($tryout->type !== 'akbar') abort(404);

        // Mengambil ID transaksi TERAKHIR untuk setiap user di tryout ini
        $latestTransactionIds = Transaction::where('tryout_id', $tryout->id)
            ->selectRaw('MAX(id) as id')
            ->groupBy('user_id')
            ->pluck('id');

        $query = Transaction::whereIn('id', $latestTransactionIds)
            ->with('user');

        // Filter Status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        } else {
            // Sorting: Pending paling atas, lalu Failed, lalu Paid
            $query->orderByRaw("FIELD(status, 'pending', 'failed', 'paid')");
        }

        // Search berdasarkan nama atau email user
        if ($request->has('search') && $request->search != '') {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $participants = $query->paginate(20)->withQueryString();

        // Hitung statistik realtime
        $baseStats = Transaction::whereIn('id', $latestTransactionIds);
        
        $stats = [
            'total' => (clone $baseStats)->count(),
            'pending' => (clone $baseStats)->where('status', 'pending')->count(),
            'paid' => (clone $baseStats)->where('status', 'paid')->count(),
        ];

        return Inertia::render('Admin/TryoutAkbar/Verify', [
            'tryout' => $tryout,
            'participants' => $participants,
            'stats' => $stats,
            'filters' => [
                'search' => $request->search ?? '',
                'status' => $request->status ?? ''
            ]
        ]);
    }

    public function verifyRegistration(Request $request, Transaction $transaction)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'reason' => 'nullable|string|max:255'
        ]);

        if ($request->action === 'approve') {
            $transaction->update([
                'status' => 'paid',
                'rejection_note' => null
            ]);
            $message = 'Peserta berhasil diverifikasi (Diterima).';
        } else {
            $transaction->update([
                'status' => 'failed',
                'rejection_note' => $request->reason ?? 'Bukti tidak valid.'
            ]);
            $message = 'Peserta ditolak.';
        }

        return back()->with('success', $message);
    }

    public function create()
    {
        return Inertia::render('Admin/TryoutAkbar/Create');
    }

    public function store(Request $request)
    {
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

        $validated['type'] = 'akbar';
        $validated['is_published'] = $request->boolean('is_published', false);

        Tryout::create($validated);

        return redirect()->route('admin.tryout-akbar.index')
            ->with('success', 'Event Tryout Akbar berhasil dibuat.');
    }

    public function edit($id)
    {
        $tryout = Tryout::where('id', $id)->where('type', 'akbar')->firstOrFail();

        return Inertia::render('Admin/TryoutAkbar/Edit', [
            'tryout' => $tryout
        ]);
    }

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

        $validated['is_published'] = $request->boolean('is_published');
        $tryout->update($validated);

        return redirect()->route('admin.tryout-akbar.index')
            ->with('success', 'Event Tryout Akbar berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $tryout = Tryout::where('id', $id)->where('type', 'akbar')->firstOrFail();
        
        if ($tryout->transactions()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus event yang sudah memiliki peserta.');
        }

        $tryout->delete();

        return back()->with('success', 'Event Tryout Akbar berhasil dihapus.');
    }
}