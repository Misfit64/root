<?php

namespace App\Livewire\Person\Actions;

use Livewire\Component;
use App\Models\FamilyTree;
use App\Models\Person;
use App\Actions\People\AddChildAction;
use App\Enums\RelationshipSubType;
use Illuminate\Validation\Rules\Enum;

class AddChildForm extends Component
{
    public FamilyTree $familyTree;
    public Person $person;
    
    public $childId = null;
    public $subtype = RelationshipSubType::Biological->value;

    public $otherParentId = null;
    public $potentialOtherParents = [];

    public function mount(FamilyTree $familyTree, Person $person)
    {
        $this->familyTree = $familyTree;
        $this->person = $person;
        
        $this->potentialOtherParents = $this->person->spouses;
        
        // Default to the first spouse if there is exactly one
        if ($this->potentialOtherParents->count() === 1) {
            $this->otherParentId = $this->potentialOtherParents->first()->id;
        }
    }

    public $activeTab = 'search'; // 'search' or 'create'

    // New Person Fields
    public $newPerson = [
        'first_name' => '',
        'last_name' => '',
        'gender' => '',
        'birth_date' => null,
        'death_date' => null,
        'notes' => '',
    ];

    public $showConfirmationModal = false;
    public $confirmationMessage = '';

    public function save(AddChildAction $addChildAction)
    {
        if ($this->activeTab === 'search') {
            $this->validate([
                'childId' => 'required|exists:people,id',
                'subtype' => ['required', new Enum(RelationshipSubType::class)],
                'otherParentId' => 'nullable|exists:people,id',
            ]);
            $child = Person::findOrFail($this->childId);
        } else {
            $this->validate([
                'newPerson.first_name' => 'required|string|max:255',
                'newPerson.last_name' => 'nullable|string|max:255',
                'newPerson.gender' => ['required', new \Illuminate\Validation\Rules\Enum(\App\Enums\Gender::class)],
                'newPerson.birth_date' => 'nullable|date',
                'newPerson.death_date' => 'nullable|date|after_or_equal:newPerson.birth_date',
                'newPerson.notes' => 'nullable|string',
                'subtype' => ['required', new Enum(RelationshipSubType::class)],
                'otherParentId' => 'nullable|exists:people,id',
            ]);

            $child = new Person([
                'family_tree_id' => $this->familyTree->id,
                'first_name' => $this->newPerson['first_name'],
                'last_name' => $this->newPerson['last_name'],
                'gender' => $this->newPerson['gender'],
                'birth_date' => $this->newPerson['birth_date'],
                'death_date' => $this->newPerson['death_date'],
                'notes' => $this->newPerson['notes'],
            ]);
        }

        // Biological Child Date Validation
        if ($this->subtype === RelationshipSubType::Biological->value) {
            $childBirthDate = $child->birth_date;
            $parentBirthDate = $this->person->birth_date;

            // Parent (this->person) should be born BEFORE Child ($child)
            // If Parent > Child, warn.
            if ($parentBirthDate && $childBirthDate && $parentBirthDate > $childBirthDate) {
                if (! $this->showConfirmationModal) {
                    $this->confirmationMessage = "This child is born before the parent ({$parentBirthDate->format('Y-m-d')}). Are you sure?";
                    $this->showConfirmationModal = true;
                    return;
                }
            }
        }

        if ($this->activeTab === 'create') {
            $child->save();
        }

        $subtypeEnum = RelationshipSubType::from($this->subtype);

        try {
            // Add relationship to current person
            $addChildAction->handle($this->person, $child, $subtypeEnum);

            // Add relationship to other parent if selected
            if ($this->otherParentId) {
                $otherParent = Person::find($this->otherParentId);
                if ($otherParent) {
                    $addChildAction->handle($otherParent, $child, $subtypeEnum);
                }
            }

            $this->dispatch('person-updated');
            $this->dispatch('child-added');
            
            $this->reset(['childId', 'subtype', 'newPerson', 'activeTab', 'showConfirmationModal', 'confirmationMessage', 'otherParentId']);
            
            // Re-populate potential parents and default
            $this->potentialOtherParents = $this->person->spouses;
            if ($this->potentialOtherParents->count() === 1) {
                $this->otherParentId = $this->potentialOtherParents->first()->id;
            }
            
        } catch (\Exception $e) {
            $this->addError('childId', $e->getMessage());
        }
    }

    public function confirmSave()
    {
        $this->save(app(AddChildAction::class));
    }

    public function cancelSave()
    {
        $this->showConfirmationModal = false;
        $this->confirmationMessage = '';
    }

    public function render()
    {
        return view('livewire.person.actions.add-child-form');
    }
}
