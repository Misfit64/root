<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Relationship extends Model
{
    protected $fillable = [
        'family_tree_id',
        'person_id',
        'relative_id',
        'relationship_type',
        'relationship_subtype',
        'notes',
    ];

    protected $casts = [
        'relationship_type' => RelationshipType::class,
        'relationship_subtype' => RelationshipSubType::class,
    ];

    
}
