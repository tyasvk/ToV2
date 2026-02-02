<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id', 
        'tryout_id', 
        'invoice_code', 
        'amount', 
        'unit_price', 
        'qty', 
        'participants_data', 
        'status', 
        'snap_token',
        'balance',                 // <--- PASTIKAN ADA
        'membership_expires_at',   // <--- WAJIB DITAMBAHKAN!
        'proof_payment', // <--- WAJIB DITAMBAHKAN DI SINI
        'rejection_note', // <--- TAMBAHKAN INI
        'total_amount',     // <--- WAJIB ADA (Untuk harga kolektif)
        'details',          // <--- WAJIB ADA (Untuk simpan email teman)
        'description', // Tambahkan ini
        'metadata',    // Tambahkan ini
        'payment_method', // <--- TAMBAHKAN INI WAJIB!
    ];

    protected $casts = [
        'participants_data' => 'array',
        'proof_payment' => 'array', 
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'metadata' => 'array', // Agar metadata otomatis jadi array/object
        'membership_expires_at' => 'datetime', // <--- WAJIB AGAR DIANGGAP TANGGAL
    ];

    // app/Models/Transaction.php

public function package()
{
    // Sesuaikan foreign_key-nya, misalnya 'package_id'
    return $this->belongsTo(Package::class, 'package_id');
}

public function tryout()
{
    return $this->belongsTo(Tryout::class, 'tryout_id');
}

    public function user() { return $this->belongsTo(User::class); }
}