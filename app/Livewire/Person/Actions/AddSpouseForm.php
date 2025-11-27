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

    public function save(AddSpouseAction $addSpouseAction)
    {
        if ($this->activeTab === 'search') {
            $this->validate([
                'spouseId' => 'required|exists:people,id',
            ]);
            $spouse = Person::findOrFail($this->spouseId);
        } else {
            $this->validate([
                'newPerson.first_name' => 'required|string|max:255',
                'newPerson.last_name' => 'nullable|string|max:255',
                'newPerson.gender' => ['required', new \Illuminate\Validation\Rules\Enum(\App\Enums\Gender::class)],
                'newPerson.birth_date' => 'nullable|date',
                'newPerson.death_date' => 'nullable|date|after_or_equal:newPerson.birth_date',
                'newPerson.notes' => 'nullable|string',
            ]);

            $spouse = Person::create([
                'family_tree_id' => $this->familyTree->id,
                'first_name' => $this->newPerson['first_name'],
                'last_name' => $this->newPerson['last_name'],
                'gender' => $this->newPerson['gender'],
                'birth_date' => $this->newPerson['birth_date'],
                'death_date' => $this->newPerson['death_date'],
                'notes' => $this->newPerson['notes'],
            ]);
        }

        $addSpouseAction->handle($this->person, $spouse);

        $this->dispatch('person-updated'); 
        $this->dispatch('spouse-added');

        $this->reset(['spouseId', 'newPerson', 'activeTab']);
    }

    public function render()
    {
        return view('livewire.person.actions.add-spouse-form');
    }

}
