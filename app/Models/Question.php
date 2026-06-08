<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    protected $fillable = [
        'tryout_id', 
        'type', 
        'content', 
        'image', 
        'options', 
        'option_images', // <--- TAMBAHKAN INI DI SINI
        'correct_answer', 
        'tkp_scores', 
        'explanation',
        'order',
    ];

    /**
     * Casting otomatis untuk kolom array/json
     */
    protected $casts = [
        'options' => 'array',
        'tkp_scores' => 'array',
        'option_images' => 'array', // Tambahkan ini
    ];

    /**
     * Relasi ke Tryout
     */
    public function tryout(): BelongsTo
    {
        return $this->belongsTo(Tryout::class);
    }
}