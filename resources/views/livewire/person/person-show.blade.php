<div class="pb-12">
    <x-slot name="breadcrumbs">
        <a href="{{ route('tree.show', $tree) }}" class="hover:underline">{{ $tree->name }}</a>
        <span class="mx-2">/</span>
        <span class="font-semibold">{{ $person->full_name }}</span>
    </x-slot>

    {{-- Header Section --}}
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6 flex flex-col md:flex-row items-center md:items-start gap-6">
        {{-- Photo --}}
        <div class="flex-shrink-0">
        <div class="flex-shrink-0">
            <img class="h-32 w-32 rounded-full object-cover border-4 border-gray-200 dark:border-gray-700 shadow-sm" src="{{ $person->default_photo_url }}" alt="{{ $person->full_name }}">
        </div>
        </div>

        {{-- Info --}}
        <div class="flex-grow text-center md:text-left">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ $person->full_name }}</h1>
            
            <div class="text-gray-600 dark:text-gray-400 space-y-1 mb-4">
                <div class="flex items-center">
                    <span>{{ $person->gender?->name ?? 'Unknown Gender' }}</span>
                    @if($person->gender?->name === 'Male')
                        <span class="text-3xl">♂</span>
                    @elseif($person->gender?->name === 'Female')
                        <span class="text-3xl">♀</span>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-medium">Born:</span> 
                    {{ $person->birth_date?->format('M j, Y') ?? 'Unknown' }}
                    @if($person->birth_date)
                        <span class="text-sm">(Age {{ $person->death_date ? (int) $person->birth_date->diffInYears($person->death_date, false) : $person->birth_date->age }})</span>
                    @endif
                </div>
                @if($person->death_date)
                    <div>
                        <span class="font-medium">Died:</span> 
                        {{ $person->death_date->format('M j, Y') }}
                    </div>
                @endif
            </div>

            @if($person->notes)
                <div class="text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900 p-3 rounded italic max-w-2xl">
                    "{{ $person->notes }}"
                </div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="flex flex-col gap-2 w-full md:w-auto">
            <button wire:click="openEditPerson" class="flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                Edit Profile
            </button>
            <a href="{{ route('person.merge.select', ['tree' => $tree->id, 'person' => $person->id]) }}" class="flex items-center justify-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                Merge Duplicate
            </a>
            <a href="{{ route('tree.graph', ['tree' => $tree->id, 'person' => $person->id]) }}" class="flex items-center justify-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                View Tree
            </a>
        </div>
    </div>

    {{-- Relationships Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        {{-- Parents --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 flex flex-col h-full">
            <div class="flex justify-between items-center mb-4 pb-2 border-b dark:border-gray-700">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Parents
                </h2>
                <button wire:click="openAddParent" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">+ Add</button>
            </div>
            
            <ul class="space-y-3 flex-grow">
                @forelse($this->parents as $p)
                    <li class="flex justify-between items-start p-2 rounded hover:bg-gray-50 dark:hover:bg-gray-700 transition group">
                        <a href="{{ route('person.show', ['tree' => $tree->id, 'person' => $p->id]) }}" class="flex items-center gap-3">
                            <img class="w-8 h-8 rounded-full object-cover" src="{{ $p->default_photo_url }}" alt="">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $p->full_name }}</div>
                                @if($p->birth_date)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $p->birth_date->format('Y') }} (Age {{ $p->death_date ? (int) $p->birth_date->diffInYears($p->death_date, false) : $p->birth_date->age }})</div>
                                @endif
                            </div>
                        </a>
                        <button 
                            wire:click="confirmRemoveParent({{ $p->id }})"
                            class="opacity-0 group-hover:opacity-100 text-red-500 hover:text-red-700 p-1"
                            title="Remove"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </li>
                @empty
                    <li class="text-gray-400 text-sm italic text-center py-4">No parents added.</li>
                @endforelse
            </ul>
        </div>

        {{-- Spouses --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 flex flex-col h-full">
            <div class="flex justify-between items-center mb-4 pb-2 border-b dark:border-gray-700">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                    Spouses
                </h2>
                <button wire:click="openAddSpouse" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">+ Add</button>
            </div>

            <ul class="space-y-3 flex-grow">
                @forelse($this->spouses as $s)
                    <li class="flex justify-between items-start p-2 rounded hover:bg-gray-50 dark:hover:bg-gray-700 transition group">
                        <a href="{{ route('person.show', ['tree' => $tree->id, 'person' => $s->id]) }}" class="flex items-center gap-3">
                            <img class="w-8 h-8 rounded-full object-cover" src="{{ $s->default_photo_url }}" alt="">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $s->full_name }}</div>
                                @if($s->birth_date)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $s->birth_date->format('Y') }} (Age {{ $s->death_date ? (int) $s->birth_date->diffInYears($s->death_date, false) : $s->birth_date->age }})</div>
                                @endif
                            </div>
                        </a>
                        <button 
                            wire:click="confirmRemoveSpouse({{ $s->id }})"
                            class="opacity-0 group-hover:opacity-100 text-red-500 hover:text-red-700 p-1"
                            title="Remove"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </li>
                @empty
                    <li class="text-gray-400 text-sm italic text-center py-4">No spouses added.</li>
                @endforelse
            </ul>
        </div>

        {{-- Children --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 flex flex-col h-full">
            <div class="flex justify-between items-center mb-4 pb-2 border-b dark:border-gray-700">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path></svg>
                    Children
                </h2>
                <button wire:click="openAddChild" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">+ Add</button>
            </div>

            <ul class="space-y-3 flex-grow">
                @forelse($this->children as $c)
                    <li class="flex justify-between items-start p-2 rounded hover:bg-gray-50 dark:hover:bg-gray-700 transition group">
                        <a href="{{ route('person.show', ['tree' => $tree->id, 'person' => $c->id]) }}" class="flex items-center gap-3">
                            <img class="w-8 h-8 rounded-full object-cover" src="{{ $c->default_photo_url }}" alt="">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $c->full_name }}</div>
                                @if($c->birth_date)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $c->birth_date->format('Y') }} (Age {{ $c->death_date ? (int) $c->birth_date->diffInYears($c->death_date, false) : $c->birth_date->age }})</div>
                                @endif
                            </div>
                        </a>
                        <button 
                            wire:click="confirmRemoveChild({{ $c->id }})"
                            class="opacity-0 group-hover:opacity-100 text-red-500 hover:text-red-700 p-1"
                            title="Remove"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </li>
                @empty
                    <li class="text-gray-400 text-sm italic text-center py-4">No children added.</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- Modals --}}
    @if($showAddSpouse)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 transition-opacity">
            <div class="bg-white dark:bg-gray-800 w-full max-w-lg rounded-xl shadow-2xl p-6 max-h-[90vh] overflow-y-auto transform transition-all scale-100">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Add Spouse</h2>
                    <button wire:click="closeAddSpouse" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <livewire:person.actions.add-spouse-form :person="$person" :familyTree="$tree" />
            </div>
        </div>
    @endif

    @if($showAddParent)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 transition-opacity">
            <div class="bg-white dark:bg-gray-800 w-full max-w-lg rounded-xl shadow-2xl p-6 max-h-[90vh] overflow-y-auto transform transition-all scale-100">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Add Parent</h2>
                    <button wire:click="closeAddParent" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <livewire:person.actions.add-parent-form :person="$person" :familyTree="$tree" />
            </div>
        </div>
    @endif

    @if($showAddChild)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 transition-opacity">
            <div class="bg-white dark:bg-gray-800 w-full max-w-lg rounded-xl shadow-2xl p-6 max-h-[90vh] overflow-y-auto transform transition-all scale-100">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Add Child</h2>
                    <button wire:click="closeAddChild" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <livewire:person.actions.add-child-form :person="$person" :familyTree="$tree" />
            </div>
        </div>
    @endif

    {{-- Modal (Edit Person) --}}
    @if($showEditPerson)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 transition-opacity">
            <div class="bg-white dark:bg-gray-800 w-full max-w-2xl rounded-xl shadow-2xl p-6 max-h-[90vh] overflow-y-auto transform transition-all scale-100">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Edit Profile</h2>
                    <button wire:click="closeEditPerson" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <livewire:person.actions.edit-person-form :person="$person" />
            </div>
        </div>
    @endif



    <x-modal.delete-confirmation 
        :show="$showDeleteConfirmation" 
        title="Remove Relationship?" 
        message="Are you sure you want to remove this relationship? This will not delete the person, only the link between them."
        onConfirm="executeDelete"
        onCancel="cancelDelete"
    />

</div>
