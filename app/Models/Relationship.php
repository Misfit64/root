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
    
    public function familyTree()
    {
        return $this->belongsTo(FamilyTree::class);
    }

    public function person()
    {
        return $this->belongsTo(Person::class,'person_id');
    }

    public function relative()
    {
        return $this->belongsTo(Person::class, 'relative_id');
    }

    #[Scope]
    public function parents(Builder $query)
    {
        return $query->where('relationship_type', RelationshipType::Parent->value);
    }

    #[Scope]
    public function children(Builder $query)
    {
        return $query->where('relationship_type', RelationshipType::Child->value);
    }

    #[Scope]
    public function spouses(Builder $query)
    {
        return $query->where('relationship_type', RelationshipType::Spouse->value);
    }
}
