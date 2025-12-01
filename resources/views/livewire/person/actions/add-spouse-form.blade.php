<div class="p-4 border rounded bg-white dark:bg-gray-800 dark:border-gray-700 space-y-4">

    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Add Spouse</h2>

    {{-- Tabs --}}
    <div class="flex border-b border-gray-200 dark:border-gray-700 mb-4">
        <button wire:click="$set('activeTab', 'search')"
            class="px-4 py-2 text-sm font-medium {{ $activeTab === 'search' ? 'text-blue-600 border-b-2 border-blue-600 dark:text-blue-400 dark:border-blue-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
            Search Existing
        </button>
        <button wire:click="$set('activeTab', 'create')"
            class="px-4 py-2 text-sm font-medium {{ $activeTab === 'create' ? 'text-blue-600 border-b-2 border-blue-600 dark:text-blue-400 dark:border-blue-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
            Create New
        </button>
    </div>

    @if($activeTab === 'search')
        {{-- Search box --}}
        <livewire:person.actions.person-search wire:model="spouseId" :tree="$familyTree" />
        @error('spouseId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    @else
        {{-- Create New Person Form --}}
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">First Name</label>
                    <input type="text" wire:model="newPerson.first_name"
                        class="border border-gray-300 dark:border-gray-600 rounded p-2 w-full bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    @error('newPerson.first_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name</label>
                    <input type="text" wire:model="newPerson.last_name"
                        class="border border-gray-300 dark:border-gray-600 rounded p-2 w-full bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    @error('newPerson.last_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Gender</label>
                <select wire:model="newPerson.gender"
                    class="border border-gray-300 dark:border-gray-600 rounded p-2 w-full bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select Gender</option>
                    @foreach(App\Enums\Gender::cases() as $g)
                        <option value="{{ $g->value }}">{{ $g->name }}</option>
                    @endforeach
                </select>
                @error('newPerson.gender') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Birth Date</label>
                    <input type="date" wire:model="newPerson.birth_date"
                        class="border border-gray-300 dark:border-gray-600 rounded p-2 w-full bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    @error('newPerson.birth_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Death Date</label>
                    <input type="date" wire:model="newPerson.death_date"
                        class="border border-gray-300 dark:border-gray-600 rounded p-2 w-full bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    @error('newPerson.death_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                <textarea wire:model="newPerson.notes"
                    class="border border-gray-300 dark:border-gray-600 rounded p-2 w-full bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                    rows="2"></textarea>
                @error('newPerson.notes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>
    @endif

    {{-- Subtype Dropdown --}}
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Relationship Type</label>
        <select wire:model="subtype"
            class="border border-gray-300 dark:border-gray-600 rounded p-2 w-full bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
            @foreach(App\Enums\RelationshipSubType::cases() as $type)
                @if(in_array($type, [App\Enums\RelationshipSubType::Married, App\Enums\RelationshipSubType::Separated]))
                    <option value="{{ $type->value }}">{{ $type->name }}</option>
                @endif
            @endforeach
        </select>
        @error('subtype') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- Submit button --}}
    <button class="px-4 py-2 !bg-blue-600 hover:!bg-blue-700 text-white rounded transition w-full"
        style="background-color: #2563eb !important;" wire:click="save">
        {{ $activeTab === 'search' ? 'Add Selected Spouse' : 'Create & Add Spouse' }}
    </button>

</div>