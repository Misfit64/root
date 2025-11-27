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
    public $showDeleteConfirmation = false;

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
            'death_date' => 'nullable|date|after_or_equal:birth_date',
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

    public function deletePerson()
    {
        // Relationships are handled by database cascade on delete, 
        // but we should double check if we need to do anything manual.
        // The migration has ->constrained('people')->cascadeOnDelete() for relative_id
        // But for person_id, we also need to make sure.
        
        // Actually, let's look at the migration.
        // If we delete a person, all relationships where they are person_id OR relative_id should be deleted.
        // Laravel's cascadeOnDelete() on foreign keys handles this at the DB level.
        
        // However, we might want to clean up the photo if it exists.
        if ($this->person->photo_path) {
            Storage::disk('public')->delete($this->person->photo_path);
        }

        $treeId = $this->person->family_tree_id;
        $this->person->delete();

        $this->dispatch('person-deleted'); // Optional, if we want to refresh the list
        
        // Redirect to the tree view since the person is gone
        return redirect()->route('tree.show', $treeId);
    }

    public function render()
    {
        return view('livewire.person.actions.edit-person-form');
    }
}
