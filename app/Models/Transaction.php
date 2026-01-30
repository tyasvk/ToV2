<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id', 'tryout_id', 'invoice_code', 
        'amount', 'unit_price', 'qty', 
        'participants_data', 'status', 'snap_token'
    ];

    protected $casts = [
        'participants_data' => 'array', // Agar otomatis jadi Array saat diambil
        'proof_payment' => 'array', // Agar otomatis jadi JSON saat disimpan, dan jadi Array saat diambil
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function tryout() { return $this->belongsTo(Tryout::class); }
}