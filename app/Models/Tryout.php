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
    ];

    protected $casts = [
        // PERBAIKAN: Hapus \TH:i agar menggunakan format standar serializeDate di bawah
        'published_at' => 'datetime',
        'started_at'   => 'datetime',
        'ended_at'     => 'datetime', 
        'end_date'     => 'datetime', 
        
        'is_active'    => 'boolean',
        'is_published' => 'boolean',
        'is_paid'      => 'boolean',   
        'price'        => 'decimal:2',    
    ];

    /**
     * Memaksa format waktu menjadi YYYY-MM-DD HH:MM:SS tanpa huruf T dan Z.
     * Ini memastikan Vue merendernya sebagai Waktu Lokal (WIB/WITA/WIT).
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

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
     */
    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }
}