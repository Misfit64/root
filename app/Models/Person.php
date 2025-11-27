<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Enums\Gender;
use App\Enums\RelationshipType;
use App\Models\FamilyTree;
use App\Models\Relationship;

class Person extends Model
{
    protected $fillable = [
        'family_tree_id',
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
        'birth_date' => 'date',
        'death_date' => 'date',
    ];


    public function parents()
    {
        return $this->belongsToMany(Person::class, 'relationships', 'person_id', 'relative_id')
            ->where('relationship_type', RelationshipType::Parent->value);
    }

    public function children()
    {
        return $this->belongsToMany(Person::class, 'relationships', 'person_id', 'relative_id')
            ->where('relationship_type', RelationshipType::Child->value);
    }

    public function spouses()
    {
        return $this->belongsToMany(Person::class, 'relationships', 'person_id', 'relative_id')
            ->where('relationship_type', RelationshipType::Spouse->value);
    }

    public function siblings()
    {
        $parents = $this->parents;

        $siblings = collect();

        foreach ($parents as $parent) {
            $siblings = $siblings->merge($parent->children);
        }

        return $siblings->unique('id')->where('id', '!=', $this->id);
    }

    public function canonicalPerson()
    {
        return $this->belongsTo(Person::class, 'canonical_person_id');
    }

    public function familyTree()
    {
        return $this->belongsTo(FamilyTree::class);
    }

    public function relationships()
    {
        return $this->hasMany(Relationship::class);
    }

    public function fullName():Attribute
    {
        return Attribute::make(
            get: fn () => trim($this->first_name . ' ' . ($this->last_name ?? '')),
        );
    }

    public function getDefaultPhotoUrlAttribute()
    {
        if ($this->photo_path) {
            return \Illuminate\Support\Facades\Storage::url($this->photo_path);
        }

        return match ($this->gender) {
            Gender::Male => asset('images/defaults/male.svg'),
            Gender::Female => asset('images/defaults/female.svg'),
            default => asset('images/defaults/unknown.svg'),
        };
    }
}
