<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tryout extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $fillable = [
        'title', 
        'description', 
        'duration_minutes', 
        'is_active', 
        'is_published', 
        'is_paid',      
        'price',         
        'published_at', 
        'started_at'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'started_at' => 'datetime',
        'is_active' => 'boolean',
        'is_published' => 'boolean',
        'is_paid' => 'boolean',   
        'price' => 'decimal:2',    
    ];

    /**
     * Relasi ke tabel transactions
     * Satu Tryout bisa memiliki banyak Transaksi
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Relasi ke model Question
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Relasi ke model ExamAttempt (Riwayat Pengerjaan)
     * INI YANG HILANG SEBELUMNYA
     */
    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }
}