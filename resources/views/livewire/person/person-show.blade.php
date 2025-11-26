@if($showAddSpouse)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">

        <div class="bg-white w-full max-w-lg rounded shadow-lg p-4">

            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Add Spouse</h2>
                <button wire:click="closeAddSpouse" class="text-gray-500 hover:text-black">✕</button>
            </div>

            <livewire:person.actions.add-spouse-form 
                :family-tree="$tree"
                :person="$person"
            />

        </div>

    </div>
@endif
