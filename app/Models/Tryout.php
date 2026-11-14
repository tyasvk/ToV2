<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tryout extends Model
{
    use HasFactory;

    // WAJIB: Definisikan hanya kolom yang ada di database
    protected $fillable = [
        'title', 
        'skb_category_id',
        'description', 
        'duration',      // HANYA gunakan 'duration'
        'is_published', 
        'is_paid',      
        'price',         
        'published_at', 
        'started_at',            
        'type',          
        'requirements',   
        'ended_at',      
        'end_date',              
        'category',              
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
        
        'is_published'          => 'boolean',
        'is_paid'               => 'boolean',   
        'price'                 => 'decimal:2',    
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // Tambahkan method relasi:
public function skbCategory()
{
    return $this->belongsTo(SkbCategory::class, 'skb_category_id');
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