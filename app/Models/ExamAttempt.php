<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamAttempt extends Model
{
    use HasFactory;

    // Pastikan angka passing grade disesuaikan jika ada settingan khusus
    public const PASSING_GRADE_TWK = 65;
    public const PASSING_GRADE_TIU = 80;
    public const PASSING_GRADE_TKP = 166;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'tryout_id',
        'answers',
        'twk_score',
        'tiu_score',
        'tkp_score',
        'total_score',
        'status',
        'completed_at', // <--- WAJIB ADA AGAR WAKTU TERSIMPAN!
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'answers' => 'array', // Jika format jawabannya JSON
        'completed_at' => 'datetime',
    ];

    // Relasi ke tabel User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke tabel Tryout
    public function tryout()
    {
        return $this->belongsTo(Tryout::class);
    }
    
    // Virtual attribute untuk is_passed
    public function getIsPassedAttribute()
    {
        return $this->twk_score >= self::PASSING_GRADE_TWK && 
               $this->tiu_score >= self::PASSING_GRADE_TIU && 
               $this->tkp_score >= self::PASSING_GRADE_TKP;
    }
}