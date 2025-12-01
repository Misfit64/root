<?php

namespace App\Livewire\Person\Actions;

use Livewire\Component;
use App\Models\Person;
use App\Enums\RelationshipType;
use App\Enums\RelationshipSubType;
use App\Actions\People\UpdateRelationshipAction;
use Illuminate\Validation\Rules\Enum;

class EditRelationForm extends Component
{
    public Person $person;
    public Person $relatedPerson;
    public RelationshipType $relationType;

    public $subtype;
    public $otherParentId;
    public $potentialOtherParents = [];

    public function mount(Person $person, Person $relatedPerson, RelationshipType $relationType)
    {
        $this->person = $person;
        $this->relatedPerson = $relatedPerson;
        $this->relationType = $relationType;

        // Fetch current subtype
        $relationship = $this->person->relationships()
            ->where('relative_id', $this->relatedPerson->id)
            ->where('relationship_type', $this->relationType->value)
            ->first();

        $this->subtype = $relationship?->relationship_subtype->value ?? RelationshipSubType::Unknown->value;

        // If editing a child, fetch potential other parents (spouses of the current person)
        if ($this->relationType === RelationshipType::Child) {
            $this->potentialOtherParents = $this->person->spouses()
                ->get()
                ->map(function ($spouse) {
                    return [
                        'id' => $spouse->id,
                        'name' => $spouse->full_name,
                    ];
                })
                ->toArray();
        }
    }

    public function save(UpdateRelationshipAction $action)
    {
        $this->validate([
            'subtype' => ['required', new Enum(RelationshipSubType::class)],
            'otherParentId' => ['nullable', 'exists:people,id'],
        ]);

        $newSubtype = RelationshipSubType::from($this->subtype);

        $action->handle($this->person, $this->relatedPerson, $this->relationType, $newSubtype);

        // Handle Other Parent linking
        if ($this->relationType === RelationshipType::Child && $this->otherParentId) {
            $otherParent = Person::find($this->otherParentId);
            if ($otherParent) {
                // Link the child to the other parent
                // We use AddParentAction logic here. 
                // Since we are in EditRelationForm, we might not have AddParentAction injected.
                // Let's resolve it or use the logic directly. 
                // Ideally, we should inject AddParentAction into the save method.

                $addParentAction = app(\App\Actions\People\AddParentAction::class);
                try {
                    // The child is $this->relatedPerson
                    // The new parent is $otherParent
                    $addParentAction->handle($this->relatedPerson, $otherParent, $newSubtype);
                } catch (\Exception $e) {
                    // Handle potential errors (e.g. already a parent, cycle, etc.)
                    // For now, we might just log or ignore if it fails silently, 
                    // or add an error bag.
                    $this->addError('otherParentId', $e->getMessage());
                    return;
                }
            }
        }

        $this->dispatch('person-updated');
        $this->dispatch('relation-updated');
    }

    public function render()
    {
        return view('livewire.person.actions.edit-relation-form');
    }
}
