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
        'type',           // <--- WAJIB DITAMBAH
        'requirements',   // <--- WAJIB DITAMBAH
        'ended_at',       // <--- WAJIB DITAMBAH
        'duration',       // <--- WAJIB DITAMBAH (Sesuaikan dengan Controller)
        'end_date',       // Tambahkan ini
        // 'duration',    // Dihapus karena duplikat di atas
    ];

    protected $casts = [
        // PERBAIKAN: Tambahkan format :Y-m-d\TH:i agar jam tidak berubah-ubah saat diedit
        'published_at' => 'datetime:Y-m-d\TH:i',
        'started_at'   => 'datetime:Y-m-d\TH:i',
        'ended_at'     => 'datetime:Y-m-d\TH:i', 
        'end_date'     => 'datetime:Y-m-d\TH:i', 
        
        'is_active'    => 'boolean',
        'is_published' => 'boolean',
        'is_paid'      => 'boolean',   
        'price'        => 'decimal:2',    
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