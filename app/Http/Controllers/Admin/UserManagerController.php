<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
}