<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamSession extends Model
{
    use HasFactory;

    /**
     * Atribut yang dapat diisi melalui mass assignment.
     */
    protected $fillable = [
        'user_id',
        'tryout_id',
        'status',
        'answers',
        'score_twk',
        'score_tiu',
        'score_tkp',
        'score_total',
        'started_at',
        'ended_at',
    ];

    /**
     * Casting atribut ke tipe data tertentu.
     * Sangat penting agar 'answers' otomatis menjadi array saat dipanggil di Vue.
     */
    protected $casts = [
        'answers' => 'array',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'score_total' => 'integer',
    ];

    /**
     * Relasi: Sesi ujian ini dimiliki oleh satu User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: Sesi ujian ini merujuk pada satu Paket Tryout.
     */
    public function tryout(): BelongsTo
    {
        return $this->belongsTo(Tryout::class);
    }

    /**
     * Helper: Mengecek apakah ujian sudah selesai.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}