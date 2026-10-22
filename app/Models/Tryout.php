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
        'started_at',            // Masa Pengerjaan Mulai
        'type',          
        'requirements',   
        'ended_at',      
        'duration',      
        'end_date',              // Masa Pengerjaan Selesai
        'category',              // 'skd' atau 'skb'
        'passing_grade',         // Nilai ambang batas total untuk SKB
        'registration_start_at', 
        'registration_end_at',   
        'instagram_post_url',
    ];

    protected $casts = [
        'published_at'          => 'datetime:Y-m-d H:i:s',
        'ended_at'              => 'datetime:Y-m-d H:i:s', 
        'end_date'              => 'datetime:Y-m-d H:i:s', 
        'registration_start_at' => 'datetime:Y-m-d H:i:s',
        'registration_end_at'   => 'datetime:Y-m-d H:i:s',
        'started_at'            => 'datetime:Y-m-d H:i:s',
        
        'is_active'             => 'boolean',
        'is_published'          => 'boolean',
        'is_paid'               => 'boolean',   
        'price'                 => 'decimal:2',    
        'passing_grade'         => 'integer',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order', 'asc');
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(TryoutRegistration::class);
    }
}