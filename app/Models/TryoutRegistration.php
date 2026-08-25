<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TryoutRegistration extends Model
{
    protected $fillable = [
        'user_id',
        'tryout_id',
        'proof_follow',
        'proof_comment',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $table->belongsTo(User::class);
    }

    public function tryout(): BelongsTo
    {
        return $table->belongsTo(Tryout::class);
    }
}