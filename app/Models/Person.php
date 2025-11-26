<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'birth_date',
        'death_date',
        'photo_path',
        'notes',
        'canonical_person_id',
    ];

    protected $casts = [
        'gender' => Gender::class,
    ];
}
