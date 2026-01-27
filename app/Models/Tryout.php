<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // Import class ini

class Tryout extends Model
{
    protected $fillable = [
    'title', 'description', 
    'duration_minutes', 
    'is_active', 
    'price', // Pastikan ini ada
    'is_paid',      // Tambahkan ini
    'is_published', // Pastikan ada
    'published_at', // Pastikan ada
    'started_at'    // Pastikan ada
];

protected $casts = [
    'published_at' => 'datetime',
    'started_at' => 'datetime',
    'is_active' => 'boolean',
];

    /**
     * Relasi ke model Question
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}