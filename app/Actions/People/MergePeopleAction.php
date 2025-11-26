<?php

namespace App\Actions\People;

use App\Models\Person;
use App\Models\Relationship;
use Illuminate\Support\Facades\DB;

class MergePeopleAction
{
    /**
     * Merge source person into target person.
     *
     * @param Person $target The person to keep.
     * @param Person $source The person to delete.
     * @param array $data Data to update on the target person (merged fields).
     */
    public function handle(Person $target, Person $source, array $data)
    {
        DB::transaction(function () use ($target, $source, $data) {
            // 1. Update target person with selected data
            $target->update($data);

            // 2. Move relationships
            // We need to move all relationships from source to target.
            // If target already has a relationship with the same relative and type, we skip (or it's a duplicate relationship).
            
            // Outgoing relationships (source -> relative)
            foreach ($source->relationships as $rel) {
                // Check if target already has this relationship
                $exists = Relationship::where('person_id', $target->id)
                    ->where('relative_id', $rel->relative_id)
                    ->where('relationship_type', $rel->relationship_type)
                    ->exists();

                if (!$exists) {
                    // Move relationship to target
                    $rel->update(['person_id' => $target->id]);
                } else {
                    // Target already has this relationship, so we just delete the source's version
                    $rel->delete();
                }
            }

            // Incoming relationships (relative -> source)
            // We need to find all relationships where relative_id is source->id
            $incomingRels = Relationship::where('relative_id', $source->id)->get();
            foreach ($incomingRels as $rel) {
                // Check if the person (rel->person_id) already has a relationship to target
                $exists = Relationship::where('person_id', $rel->person_id)
                    ->where('relative_id', $target->id)
                    ->where('relationship_type', $rel->relationship_type)
                    ->exists();

                if (!$exists) {
                    // Point relationship to target
                    $rel->update(['relative_id' => $target->id]);
                } else {
                    // Duplicate, delete
                    $rel->delete();
                }
            }

            // 3. Delete source person
            $source->delete();
        });
    }
}
