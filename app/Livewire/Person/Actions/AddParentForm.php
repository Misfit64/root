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

    public function save(AddParentAction $addParentAction)
    {
        $this->validate([
            'parentId' => 'required|exists:people,id',
            'subtype' => ['required', new Enum(RelationshipSubType::class)],
        ]);

        $parent = Person::findOrFail($this->parentId);
        $subtypeEnum = RelationshipSubType::from($this->subtype);

        try {
            $addParentAction->handle($this->person, $parent, $subtypeEnum);

            $this->dispatch('person-updated');
            $this->dispatch('parent-added');
            
            $this->reset(['parentId', 'subtype']);
            
        } catch (\Exception $e) {
            $this->addError('parentId', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.person.actions.add-parent-form');
    }
}
