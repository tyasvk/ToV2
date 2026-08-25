<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TryoutRegistration;
use App\Models\Transaction; // Tambahan
use Illuminate\Http\Request;
use Inertia\Inertia;

class TryoutRegistrationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $status = $request->status ?? 'pending';

        $query = TryoutRegistration::with(['user', 'tryout'])
            ->when($search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('tryout', function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%");
                });
            })
            ->when($status !== 'all', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest();

        $registrations = $query->paginate(15)->withQueryString();

        // Cek secara dinamis apakah user sudah melakukan pembayaran (Premium)
        $registrations->getCollection()->transform(function ($reg) {
            $reg->has_paid = Transaction::where('user_id', $reg->user_id)
                ->where('tryout_id', $reg->tryout_id)
                ->whereIn('status', ['paid', 'success'])
                ->exists();
            return $reg;
        });

        $pendingCount = TryoutRegistration::where('status', 'pending')->count();

        return Inertia::render('Admin/Tryout/Verifikasi', [
            'registrations' => $registrations,
            'filters' => $request->only(['search', 'status']),
            'pendingCount' => $pendingCount
        ]);
    }

    public function update(Request $request, TryoutRegistration $registration)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $registration->update([
            'status' => $request->status,
        ]);

        $message = $request->status === 'approved' 
            ? 'Verifikasi disetujui. Peserta sekarang dapat mengakses Tryout Gratis.' 
            : 'Verifikasi ditolak.';

        return back()->with('success', $message);
    }
}