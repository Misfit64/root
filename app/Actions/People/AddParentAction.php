<?php

namespace App\Actions\People;

use App\Models\Person;
use App\Models\Relationship;
use Illuminate\Support\Facades\DB;
use App\Enums\RelationshipType;
use App\Enums\RelationshipSubtype;
use Exception;

class AddParentAction
{
    public function handle(Person $child, Person $parent, RelationshipSubtype $subtype): Person
    {
        // 1. Prevent self-parenting
        if ($child->id === $parent->id) {
            throw new Exception("A person cannot be their own parent.");
        }

        // 2. They must be in the same family tree
        if ($child->family_tree_id !== $parent->family_tree_id) {
            throw new Exception("Both people must belong to the same family tree.");
        }

        // 3. Prevent cycles: parent cannot be a descendant of child
        if ($this->isDescendant($child, $parent)) {
            throw new Exception("This would create a loop in the family tree.");
        }

        // 4. Biological limit: only 2 parents allowed
        if ($subtype === RelationshipSubtype::Biological) {
            $bioCount = $child->parents()
                ->where('relationship_subtype', RelationshipSubtype::Biological->value)
                ->count();

            if ($bioCount >= 2) {
                throw new Exception("A child cannot have more than 2 biological parents.");
            }
        }

        DB::transaction(function () use ($child, $parent, $subtype) {

            // Check duplicates P → C
            $existsParent = Relationship::checkRelation($parent, $child, RelationshipType::Parent);

            // Check duplicates C → P
            $existsChild = Relationship::checkRelation($child, $parent, RelationshipType::Child);

            // Create P → C
            if (! $existsParent) {
                Relationship::create([
                    'family_tree_id'      => $child->family_tree_id,
                    'person_id'           => $parent->id,
                    'relative_id'         => $child->id,
                    'relationship_type'   => RelationshipType::Parent->value,
                    'relationship_subtype'=> $subtype->value,
                ]);
            }

            // Create C → P
            if (! $existsChild) {
                Relationship::create([
                    'family_tree_id'      => $child->family_tree_id,
                    'person_id'           => $child->id,
                    'relative_id'         => $parent->id,
                    'relationship_type'   => RelationshipType::Child->value,
                    'relationship_subtype'=> $subtype->value,
                ]);
            }
        });

        return $child->refresh();
    }

    /**
     * Recursively check if $target is a descendant of $person
     */
    private function isDescendant(Person $person, Person $target): bool
    {
        foreach ($person->children as $child) {

            if ($child->id === $target->id) {
                return true;
            }

            if ($this->isDescendant($child, $target)) {
                return true;
            }
        }

        return false;
    }
}