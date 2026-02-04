<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles; 

    protected $fillable = [
        'name', 'email', 'password', 'balance', 'avatar', 
        'membership_expires_at', 'participant_number', 'instance_type', 
        'agency_name', 'province_code', 'gender','affiliate_code',
    'affiliate_balance','bank_info',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * PERBAIKAN: Satukan semua casting di sini.
     * HAPUS properti $casts yang ada di luar fungsi ini.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'bank_info' => 'array',
            'membership_expires_at' => 'datetime', // <--- WAJIB DI SINI AGAR REAKTIF
        ];
    }

    /**
 * Relasi ke Riwayat Transaksi Dompet
 */
public function walletTransactions()
{
    return $this->hasMany(WalletTransaction::class);
}

    public function isMember()
    {
        return $this->membership_expires_at && $this->membership_expires_at->isFuture();
    }

    public function getMembershipDaysLeftAttribute(): int
    {
        return $this->membership_expires_at ? now()->diffInDays($this->membership_expires_at, false) : 0;
    }

    public function examAttempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function purchasedTryouts()
    {
        return $this->belongsToMany(Tryout::class, 'purchases')
                    ->withPivot('status')
                    ->wherePivot('status', 'success');
    }
}