<?php

namespace App\Actions\People;

use App\Models\Person;
use App\Models\Relationship;
use App\Enums\RelationshipSubType;
use App\Enums\RelationshipType;
use Illuminate\Support\Facades\DB;

class UpdateRelationshipAction
{
    public function handle(Person $person, Person $relatedPerson, RelationshipType $type, RelationshipSubType $newSubtype): void
    {
        DB::transaction(function () use ($person, $relatedPerson, $type, $newSubtype) {
            // Update A -> B
            Relationship::where('person_id', $person->id)
                ->where('relative_id', $relatedPerson->id)
                ->where('relationship_type', $type->value)
                ->update(['relationship_subtype' => $newSubtype->value]);

            // Determine reverse type
            $reverseType = match ($type) {
                RelationshipType::Parent => RelationshipType::Child,
                RelationshipType::Child => RelationshipType::Parent,
                RelationshipType::Spouse => RelationshipType::Spouse,
            };

            // Update B -> A
            Relationship::where('person_id', $relatedPerson->id)
                ->where('relative_id', $person->id)
                ->where('relationship_type', $reverseType->value)
                ->update(['relationship_subtype' => $newSubtype->value]);
        });
    }
}
