<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamilyTree extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'visibility',
    ];

    protected $casts = [
        'visibility' => TreeVisibility::class,
    ];
}
