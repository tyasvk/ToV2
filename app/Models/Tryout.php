<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tryout extends Model
{
    protected $fillable = [
        'title', 
        'description', 
        'duration_minutes', 
        'is_active', 
        'is_published', // Pastikan is_published juga ada jika digunakan
        'is_paid',      // Tambahkan ini
        'price',         // Tambahkan ini
        'published_at', 
        'started_at'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'started_at' => 'datetime',
        'is_active' => 'boolean',
        'is_paid' => 'boolean',   // Tambahkan ini agar database mengenali boolean
        'price' => 'decimal:2',    // Tambahkan ini agar format harga konsisten
    ];

    /**
     * Relasi ke model Question
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}