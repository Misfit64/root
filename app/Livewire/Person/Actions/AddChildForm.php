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

    public function mount(FamilyTree $familyTree, Person $person)
    {
        $this->familyTree = $familyTree;
        $this->person = $person;
    }

    public function save(AddChildAction $addChildAction)
    {
        $this->validate([
            'childId' => 'required|exists:people,id',
            'subtype' => ['required', new Enum(RelationshipSubType::class)],
        ]);

        $child = Person::findOrFail($this->childId);
        $subtypeEnum = RelationshipSubType::from($this->subtype);

        try {
            $addChildAction->handle($this->person, $child, $subtypeEnum);

            $this->dispatch('person-updated');
            $this->dispatch('child-added');
            
            $this->reset(['childId', 'subtype']);
            
        } catch (\Exception $e) {
            $this->addError('childId', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.person.actions.add-child-form');
    }
}
