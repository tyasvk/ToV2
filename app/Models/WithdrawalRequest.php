<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawalRequest extends Model
{
    // Pastikan semua kolom ini ada agar tidak kena MassAssignmentException
    protected $fillable = [
        'user_id', 
        'amount', 
        'bank_details', 
        'status', 
        'admin_note'
    ];

    protected $casts = [
        'bank_details' => 'array',
    ];

    // Relasi ke User harus didefinisikan
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}