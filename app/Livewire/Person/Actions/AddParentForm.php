<?php

namespace App\Livewire\Person\Actions;

use Livewire\Component;
use App\Models\FamilyTree;
use App\Models\Person;
use App\Actions\People\AddParentAction;
use App\Enums\RelationshipSubType;
use Illuminate\Validation\Rules\Enum;

class AddParentForm extends Component
{
    public FamilyTree $familyTree;
    public Person $person;
    
    public $parentId = null;
    public $subtype = RelationshipSubType::Biological->value;

    public function mount(FamilyTree $familyTree, Person $person)
    {
        $this->familyTree = $familyTree;
        $this->person = $person;
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

    public function save(AddParentAction $addParentAction)
    {
        if ($this->activeTab === 'search') {
            $this->validate([
                'parentId' => 'required|exists:people,id',
                'subtype' => ['required', new Enum(RelationshipSubType::class)],
            ]);
            $parent = Person::findOrFail($this->parentId);
        } else {
            $this->validate([
                'newPerson.first_name' => 'required|string|max:255',
                'newPerson.last_name' => 'nullable|string|max:255',
                'newPerson.gender' => ['required', new \Illuminate\Validation\Rules\Enum(\App\Enums\Gender::class)],
                'newPerson.birth_date' => 'nullable|date',
                'newPerson.death_date' => 'nullable|date|after_or_equal:newPerson.birth_date',
                'newPerson.notes' => 'nullable|string',
                'subtype' => ['required', new Enum(RelationshipSubType::class)],
            ]);

            $parent = new Person([
                'family_tree_id' => $this->familyTree->id,
                'first_name' => $this->newPerson['first_name'],
                'last_name' => $this->newPerson['last_name'],
                'gender' => $this->newPerson['gender'],
                'birth_date' => $this->newPerson['birth_date'],
                'death_date' => $this->newPerson['death_date'],
                'notes' => $this->newPerson['notes'],
            ]);
        }

        // Biological Parent Date Validation
        if ($this->subtype === RelationshipSubType::Biological->value) {
            $parentBirthDate = $parent->birth_date;
            $childBirthDate = $this->person->birth_date;

            if ($parentBirthDate && $childBirthDate && $parentBirthDate > $childBirthDate) {
                if (! $this->showConfirmationModal) {
                    $this->confirmationMessage = "This parent is born after the child ({$childBirthDate->format('Y-m-d')}). Are you sure?";
                    $this->showConfirmationModal = true;
                    return;
                }
            }
        }

        if ($this->activeTab === 'create') {
            $parent->save();
        }

        $subtypeEnum = RelationshipSubType::from($this->subtype);

        try {
            $addParentAction->handle($this->person, $parent, $subtypeEnum);

            $this->dispatch('person-updated');
            $this->dispatch('parent-added');
            
            $this->reset(['parentId', 'subtype', 'newPerson', 'activeTab', 'showConfirmationModal', 'confirmationMessage']);
            
        } catch (\Exception $e) {
            $this->addError('parentId', $e->getMessage());
        }
    }

    public function confirmSave()
    {
        // Re-run save, the modal flag will be true so it will bypass the check logic but we need to call save again.
        // Actually, better to just call save() again, but we need to make sure validation passes.
        // Since validation passed before showing modal, it should pass again.
        // However, we need to handle the 'create' logic carefully.
        // If we just call save(), it will re-validate.
        
        // Let's just call save() and rely on the flag being true.
        // But wait, save() resets the flag at the end.
        // So if I call save(), it will hit the check:
        // if (! $this->showConfirmationModal) -> false, so it skips the return.
        // Then it proceeds to save.
        
        // One issue: $parent object is local to save().
        // So we need to re-fetch or re-create it.
        // This is fine.
        
        $this->save(app(AddParentAction::class));
    }

    public function cancelSave()
    {
        $this->showConfirmationModal = false;
        $this->confirmationMessage = '';
    }

    public function render()
    {
        return view('livewire.person.actions.add-parent-form');
    }
}
