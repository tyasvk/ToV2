<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB; // Tambahkan ini
use App\Models\WalletTransaction;

class UserManagerController extends Controller
{
    public function index()
    {
        // Mengambil semua user beserta role-nya
        $users = User::with('roles')->latest()->get();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users
        ]);
    }

    public function destroy(User $user)
    {
        // Mencegah admin menghapus dirinya sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }

        $user->delete();
        return back()->with('message', 'Pengguna berhasil dihapus.');
    }

    public function update(Request $request, User $user)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,
        'role' => 'required|string|in:admin,user',
    ]);

    // Update data dasar
    $user->update([
        'name' => $request->name,
        'email' => $request->email,
    ]);

    // Sinkronisasi Role (Spatie)
    $user->syncRoles($request->role);

    return back()->with('message', 'Data pengguna dan role berhasil diperbarui!');
}

public function addBalance(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'description' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request, $user) {
            // 1. Tambah saldo ke user
            $user->increment('balance', $request->amount);

            // 2. Catat riwayat transaksi dompet (Agar user tahu darimana saldo ini)
            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'credit', // credit = masuk
                'amount' => $request->amount,
                'description' => $request->description ?? 'Topup Manual oleh Admin',
                'status' => 'success',
                'proof_payment' => 'ADMIN-MANUAL',
            ]);
        });

        return back()->with('success', 'Saldo berhasil ditambahkan sebesar Rp ' . number_format($request->amount));
    }
}