<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // Import class ini

class Tryout extends Model
{
    protected $fillable = [
    'title', 'description', 'duration_minutes', 'is_active', 'published_at', 'started_at'
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