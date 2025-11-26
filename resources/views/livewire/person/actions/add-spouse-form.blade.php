<div class="p-4 border rounded bg-white space-y-4">

    <h2 class="text-lg font-bold">Add Spouse</h2>

    {{-- Search box --}}
    <livewire:person.actions.person-search 
        wire:model="spouseId" 
        :tree="$familyTree" 
    />

    {{-- Submit button --}}
    <button
        class="px-4 py-2 bg-blue-600 text-white rounded"
        wire:click="save"
    >
        Add Spouse
    </button>

</div>
