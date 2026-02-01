<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamAttempt extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Cast answers ke array otomatis
    protected $casts = [
        'answers' => 'array',
        'completed_at' => 'datetime',
    ];

    // KONSTANTA PASSING GRADE (Ubah di sini, berubah di semua tempat)
    public const PASSING_GRADE_TWK = 65;
    public const PASSING_GRADE_TIU = 80;
    public const PASSING_GRADE_TKP = 166;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tryout()
    {
        return $this->belongsTo(Tryout::class);
    }

    // Helper untuk cek kelulusan
    public function getIsPassedAttribute()
    {
        return $this->twk_score >= self::PASSING_GRADE_TWK &&
               $this->tiu_score >= self::PASSING_GRADE_TIU &&
               $this->tkp_score >= self::PASSING_GRADE_TKP;
    }
}