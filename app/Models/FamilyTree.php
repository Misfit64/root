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
        'is_public',
        'visibility',
    ];

    protected $casts = [
        'visibility' => TreeVisibility::class,
    ];

    public function members()
    {
        return $this->belongsToMany(User::class, 'tree_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
