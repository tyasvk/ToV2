<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipPackage extends Model
{
    /**
     * Atribut yang dapat diisi secara massal (Mass Assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'price',
        'duration_days',
        'is_active',
    ];
}