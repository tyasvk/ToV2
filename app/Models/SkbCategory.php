<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkbCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tryouts()
    {
        return $this->hasMany(Tryout::class, 'skb_category_id');
    }
}