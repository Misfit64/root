<?php

namespace App\Livewire\Tree\Actions;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\FamilyTree;
use App\Models\Person;
use App\Enums\Gender;
use Illuminate\Validation\Rules\Enum;

class AddPersonToTreeForm extends Component
{
    use WithFileUploads;

    public FamilyTree $tree;

    public $first_name;
    public $last_name;
    public $gender;
    public $birth_date;
    public $notes;
    public $photo;

    public function mount(FamilyTree $tree)
    {
        $this->tree = $tree;
        $this->gender = Gender::Unknown->value;
    }

    public function save()
    {
        $validated = $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'gender' => ['nullable', new Enum(Gender::class)],
            'birth_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|max:1024', // 1MB Max
        ]);

        $personData = [
            'family_tree_id' => $this->tree->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'gender' => $this->gender ?? Gender::Unknown->value,
            'birth_date' => $this->birth_date,
            'notes' => $this->notes,
        ];

        if ($this->photo) {
            $path = $this->photo->store('people', 'public');
            $personData['photo_path'] = $path;
        }

        Person::create($personData);

        $this->reset(['first_name', 'last_name', 'birth_date', 'notes', 'photo']);
        $this->gender = Gender::Unknown->value;
        
        $this->dispatch('person-added');
    }

    public function render()
    {
        return view('livewire.tree.actions.add-person-to-tree-form');
    }
}
