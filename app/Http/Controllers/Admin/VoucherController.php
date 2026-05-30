<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class VoucherController extends Controller
{
public function index(Request $request)
    {
        // 1. Ambil batasan baris per halaman
        $perPage = $request->input('per_page', 10);
        if (!in_array($perPage, [10, 50, 100])) {
            $perPage = 10;
        }

        $query = Voucher::with('user')->latest();

        // 2. Default filter sekarang adalah 'available' jika parameter kosong
        $status = $request->input('status', 'available');
        
        if ($status === 'used') {
            $query->where('is_used', true);
        } else {
            // Jika status 'available' (atau apapun yang tidak dikenal), tampilkan yang belum dipakai
            $query->where('is_used', false);
        }

        // 3. Jalankan paginasi
        $vouchers = $query->paginate($perPage)->withQueryString();

        return Inertia::render('Admin/Voucher/Index', [
            'vouchers' => $vouchers
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'quantity' => 'required|integer|min:1|max:50', // Bisa generate banyak sekaligus
        ]);

        for ($i = 0; $i < $request->quantity; $i++) {
            Voucher::create([
                // Membuat kode random 10 karakter kapital (misal: VCH-XYZ123)
                'code' => 'VCH-' . strtoupper(Str::random(8)),
                'amount' => $request->amount,
                'is_used' => false,
            ]);
        }

        return redirect()->back()->with('success', $request->quantity . ' Voucher berhasil di-generate.');
    }

    public function destroy(Voucher $voucher)
    {
        if ($voucher->is_used) {
            return redirect()->back()->with('error', 'Voucher yang sudah digunakan tidak dapat dihapus.');
        }

        $voucher->delete();
        return redirect()->back()->with('success', 'Voucher berhasil dihapus.');
    }
}