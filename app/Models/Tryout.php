<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tryout extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'duration_minutes',
        'price',
        'is_paid',
        // Kolom Baru yang Wajib Ada:
        'is_published',
        'published_at',
        'started_at',
    ];

    /**
     * Konversi tipe data otomatis saat diakses di Vue
     */
    protected $casts = [
        'is_paid' => 'boolean',
        'is_published' => 'boolean',
        'price' => 'decimal:2',
        'published_at' => 'datetime',
        'started_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    /**
     * Relasi ke Soal
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}