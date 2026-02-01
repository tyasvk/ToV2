<?php

namespace App\Models;

// 1. TAMBAHKAN IMPORT INI DI ATAS
use Spatie\Permission\Traits\HasRoles; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    // 2. GUNAKAN TRAIT DI SINI
    use HasFactory, Notifiable, HasRoles; 

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'balance', // <-- Tambahkan ini
        'avatar', // <--- Tambahkan ini
        'membership_expires_at', // Tambahkan ini

    'agency_name', // <--- Tambahkan ini
    // --- TAMBAHKAN KOLOM BARU INI ---
        'participant_number',
        'instance_type',
        'agency_name',
        'province_code',
        'gender',
    ];

    // app/Models/User.php
protected $casts = [
    'membership_expires_at' => 'datetime',
];


// Fungsi untuk mengecek apakah user adalah member aktif
public function isMember()
{
    return $this->membership_expires_at && $this->membership_expires_at->isFuture();
}
public function getMembershipDaysLeftAttribute(): int
{
    return $this->membership_expires_at ? now()->diffInDays($this->membership_expires_at, false) : 0;
}

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
        ];
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