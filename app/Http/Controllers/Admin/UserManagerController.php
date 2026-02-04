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
// app/Http/Controllers/Admin/UserManagerController.php

public function index(Request $request)
{
    $users = User::query()
        ->with('roles')
        ->when($request->search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        })
        ->latest()
        ->get(); // Gunakan ->paginate(10) jika data user sangat banyak

    return Inertia::render('Admin/Users/Index', [
        'users' => $users,
        'filters' => $request->only(['search'])
    ]);
}

public function update(Request $request, User $user)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,
        'role' => 'required|string|in:admin,user',
    ]);

    $user->update([
        'name' => $request->name,
        'email' => $request->email,
    ]);

    // Sinkronisasi Role menggunakan Spatie (admin/user)
    $user->syncRoles($request->role);

    return back()->with('message', 'Data pengguna berhasil diperbarui.');
}

public function addBalance(Request $request, User $user)
{
    $request->validate([
        'amount' => 'required|numeric|min:1000',
        'description' => 'nullable|string|max:255',
    ]);

    DB::transaction(function () use ($request, $user) {
        $user->increment('balance', $request->amount);

        WalletTransaction::create([
            'user_id' => $user->id,
            'type' => 'credit',
            'amount' => $request->amount,
            'description' => $request->description ?? 'Topup Manual oleh Admin',
            'status' => 'success',
            'proof_payment' => 'ADMIN-MANUAL',
        ]);
    });

    return back()->with('success', 'Berhasil menambahkan saldo.');
}

public function addMembership(Request $request, User $user)
{
    $request->validate([
        'days' => 'required|integer|min:1'
    ]);

    // Ambil tanggal mulai: Jika sudah ada member aktif, mulai dari tanggal expired. 
    // Jika belum ada/sudah expired, mulai dari sekarang.
    $startDate = ($user->membership_expires_at && $user->membership_expires_at->isFuture()) 
        ? $user->membership_expires_at 
        : now();

    $user->update([
        'membership_expires_at' => $startDate->addDays($request->days)
    ]);

    return back()->with('success', "Berhasil menambahkan {$request->days} hari membership untuk {$user->name}");
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

}