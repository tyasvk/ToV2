<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'balance',
        'affiliate_code',
        'affiliate_balance',
        'membership_expires_at', // Tambahkan ini agar membership bisa diupdate
        'province',
        'city',
        'district',
        'agency_name',
        'participant_number',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'membership_expires_at' => 'datetime', // Tambahkan ini untuk pengolahan tanggal otomatis
        ];
    }

    /**
     * Cek apakah user adalah member premium yang aktif
     */
    public function isMember()
    {
        return $this->membership_expires_at && $this->membership_expires_at->isFuture();
    }

    // Relasi
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function examAttempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }
}