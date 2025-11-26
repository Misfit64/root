<?php

namespace App\Services;

use App\Models\Person;
use App\Models\Relationship;
use App\Enums\RelationshipType;
use Illuminate\Support\Facades\DB;

class RelationshipRemovalService
{
    public function removeSpouse(Person $person, Person $spouse)
    {
        DB::transaction(function () use ($person, $spouse) {
            // Remove person -> spouse
            Relationship::where('person_id', $person->id)
                ->where('relative_id', $spouse->id)
                ->where('relationship_type', RelationshipType::Spouse)
                ->delete();

            // Remove spouse -> person
            Relationship::where('person_id', $spouse->id)
                ->where('relative_id', $person->id)
                ->where('relationship_type', RelationshipType::Spouse)
                ->delete();
        });
    }

    public function removeParent(Person $child, Person $parent)
    {
        DB::transaction(function () use ($child, $parent) {
            // Remove child -> parent (Parent relationship)
            Relationship::where('person_id', $child->id)
                ->where('relative_id', $parent->id)
                ->where('relationship_type', RelationshipType::Parent)
                ->delete();

            // Remove parent -> child (Child relationship)
            Relationship::where('person_id', $parent->id)
                ->where('relative_id', $child->id)
                ->where('relationship_type', RelationshipType::Child)
                ->delete();
        });
    }

    public function removeChild(Person $parent, Person $child)
    {
        // Removing a child is the same as removing a parent, just from the other perspective
        $this->removeParent($child, $parent);
    }
}
