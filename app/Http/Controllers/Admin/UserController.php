<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Menampilkan daftar user beserta role-nya.
     */
    public function index()
    {
        // Muat user dengan relasi roles, urutkan dari yang terbaru
        $users = User::with('roles')->latest()->get();
        // Ambil semua role yang tersedia untuk dropdown di frontend
        $roles = Role::all();

        return Inertia::render('Admin/UserManagement', [
            'users' => $users,
            'roles' => $roles
        ]);
    }

    /**
     * Memperbarui role user secara dinamis.
     */
    public function updateRole(Request $request, User $user)
    {
        // Keamanan: Cegah Admin mengubah role-nya sendiri (agar tidak terkunci)
        if (Auth::id() === $user->id) {
            return back()->with('error', 'Demi keamanan, Anda tidak dapat mengubah role akun sendiri.');
        }

        // Validasi input role
        $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        // Gunakan syncRoles dari Spatie untuk menimpa role lama dengan yang baru
        $user->syncRoles($request->role);

        return back()->with('message', "Akses untuk {$user->name} berhasil diperbarui menjadi {$request->role}.");
    }

    /**
     * Menghapus akun user secara permanen.
     */
    public function destroy(User $user)
    {
        // Keamanan: Cegah Admin menghapus akunnya sendiri
        if (Auth::id() === $user->id) {
            return back()->with('error', 'Anda tidak diperbolehkan menghapus akun utama administrator.');
        }

        $user->delete();

        return back()->with('message', 'Data pengguna telah berhasil dihapus dari sistem.');
    }
}