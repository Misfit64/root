<div class="p-4 border rounded bg-white space-y-4">

    <h2 class="text-lg font-bold">Add Child</h2>

    {{-- Search box --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Search Child</label>
        <livewire:person.actions.person-search 
            wire:model="childId" 
            :tree="$familyTree" 
        />
        @error('childId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- Subtype Dropdown --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Relationship Type</label>
        <select wire:model="subtype" class="border rounded p-2 w-full">
            @foreach(App\Enums\RelationshipSubType::cases() as $type)
                <option value="{{ $type->value }}">{{ $type->name }}</option>
            @endforeach
        </select>
        @error('subtype') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- Submit button --}}
    <button
        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        wire:click="save"
    >
        Add Child
    </button>

</div>
