<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\TreeVisibility;

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
