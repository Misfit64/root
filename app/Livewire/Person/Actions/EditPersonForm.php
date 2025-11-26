<?php

namespace App\Livewire\Person\Actions;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Person;
use App\Enums\Gender;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Storage;

class EditPersonForm extends Component
{
    use WithFileUploads;

    public Person $person;

    public $first_name;
    public $last_name;
    public $gender;
    public $birth_date;
    public $death_date;
    public $notes;
    public $photo;

    public function mount(Person $person)
    {
        $this->person = $person;
        $this->first_name = $person->first_name;
        $this->last_name = $person->last_name;
        $this->gender = $person->gender?->value;
        $this->birth_date = $person->birth_date?->format('Y-m-d');
        $this->death_date = $person->death_date?->format('Y-m-d');
        $this->notes = $person->notes;
    }

    public function save()
    {
        $validated = $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'gender' => ['nullable', new Enum(Gender::class)],
            'birth_date' => 'nullable|date',
            'death_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|max:1024', // 1MB Max
        ]);

        if ($this->photo) {
            $path = $this->photo->store('people', 'public');
            $validated['photo_path'] = $path;
        }

        $this->person->update($validated);

        $this->dispatch('person-updated');
        $this->dispatch('person-edit-closed');
    }

    public function render()
    {
        return view('livewire.person.actions.edit-person-form');
    }
}
