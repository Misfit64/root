<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\RelationshipType;
use App\Enums\RelationshipSubType;
use App\Models\Person;
use App\Models\FamilyTree;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Scope;

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

    public static function checkRelation(Person $person, Person $relative, RelationshipType $relationshipType)
    {
        return self::where('person_id', $person->id)
            ->where('relative_id', $relative->id)
            ->where('relationship_type', $relationshipType->value)
            ->exists();
    }
}
