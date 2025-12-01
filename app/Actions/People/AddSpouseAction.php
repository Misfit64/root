<?php

namespace App\Actions\People;

use App\Models\Person;
use App\Models\Relationship;
use Illuminate\Support\Facades\DB;
use App\Enums\RelationshipType;
use App\Enums\RelationshipSubType;

class AddSpouseAction
{
    public function handle(Person $person, Person $spouse, RelationshipSubType $subtype): Person
    {
        if ($person->id === $spouse->id) {
            throw new \Exception("A person cannot be their own spouse.");
        }

        DB::transaction(function () use ($person, $spouse, $subtype) {

            // Check A → B
            $existsAB = Relationship::checkRelation($person, $spouse, RelationshipType::Spouse);

            // Check B → A
            $existsBA = Relationship::checkRelation($spouse, $person, RelationshipType::Spouse);

            // Create A → B if missing
            if (!$existsAB) {
                Relationship::create([
                    'family_tree_id' => $person->family_tree_id,
                    'person_id' => $person->id,
                    'relative_id' => $spouse->id,
                    'relationship_type' => RelationshipType::Spouse->value,
                    'relationship_subtype' => $subtype->value,
                ]);
            }

            // Create B → A if missing
            if (!$existsBA) {
                Relationship::create([
                    'family_tree_id' => $person->family_tree_id, // same tree
                    'person_id' => $spouse->id,
                    'relative_id' => $person->id,
                    'relationship_type' => RelationshipType::Spouse->value,
                    'relationship_subtype' => $subtype->value,
                ]);
            }
        });

        return $person->refresh();
    }
}
