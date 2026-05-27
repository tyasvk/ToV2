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
        'started_at',
        'type',           
        'requirements',   
        'ended_at',       
        'duration',       
        'end_date',       
    ];

    protected $casts = [
        // FORMAT KUNCI: Laravel dipaksa mengeluarkan output murni (Contoh: "2026-05-28 08:00:00")
        // Tidak akan ada lagi huruf 'T', 'Z', atau konversi zona waktu yang mengacaukan browser.
        'published_at' => 'datetime:Y-m-d H:i:s',
        'started_at'   => 'datetime:Y-m-d H:i:s',
        'ended_at'     => 'datetime:Y-m-d H:i:s', 
        'end_date'     => 'datetime:Y-m-d H:i:s', 
        
        'is_active'    => 'boolean',
        'is_published' => 'boolean',
        'is_paid'      => 'boolean',   
        'price'        => 'decimal:2',    
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }
}