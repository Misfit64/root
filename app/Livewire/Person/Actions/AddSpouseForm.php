<?php

namespace App\Livewire\Person\Actions;

use Livewire\Component;
use App\Models\FamilyTree;
use App\Models\Person;
use App\Actions\People\AddSpouseAction;

class AddSpouseForm extends Component
{
    public FamilyTree $familyTree;
    public Person $person;
    public $spouseId = null;

    public function mount(FamilyTree $familyTree, Person $person)
    {
        $this->familyTree = $familyTree;
        $this->person = $person;
    }

    public function save(AddSpouseAction $addSpouseAction)
    {
        $this->validate([
            'spouseId' => 'required|exists:people,id',
        ]);

        $spouse = Person::findOrFail($this->spouseId);

        $addSpouseAction->handle($this->person, $spouse);

        $this->dispatch('person-updated'); // tell PersonShow to refresh
        $this->dispatch('spouse-added');

        $this->spouseId = null; // reset the field
    }

    public function render()
    {
        return view('livewire.person.actions.add-spouse-form');
    }

}
