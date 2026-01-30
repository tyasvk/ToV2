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
        'proof_payment', // <--- WAJIB DITAMBAHKAN DI SINI
        'rejection_note' // <--- TAMBAHKAN INI
    ];

    protected $casts = [
        'participants_data' => 'array',
        'proof_payment' => 'array', 
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function tryout() { return $this->belongsTo(Tryout::class); }
}