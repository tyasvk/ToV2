<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TryoutRegistration extends Model
{
    use HasFactory;

    // Pastikan menggunakan tanda kutip untuk nama tabel
    protected $table = 'tryout_registrations';

    protected $fillable = [
        'user_id',
        'tryout_id',
        'proof_follow',
        'proof_comment',
        'status',
    ];

    /**
     * Relasi ke model User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Relasi ke model Tryout
     */
    public function tryout()
    {
        return $this->belongsTo(Tryout::class, 'tryout_id', 'id');
    }
}