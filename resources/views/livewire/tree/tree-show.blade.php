<div class="pb-12">
    <x-slot name="breadcrumbs">
        <span class="font-semibold">{{ $tree->name }}</span>
    </x-slot>

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $tree->name }}</h1>
            @if($tree->description)
                <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $tree->description }}</p>
            @endif
        </div>
        
        {{-- Add New Person Button --}}
        <button wire:click="openAddPerson" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 shadow transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add New Person
        </button>
    </div>

    {{-- Search Bar --}}
    <div class="mb-6">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search" 
                placeholder="Search people..." 
                class="w-full pl-10 border border-gray-300 dark:border-gray-700 rounded-lg p-3 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm"
            >
        </div>
    </div>

    {{-- People List --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Gender</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Birth Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($people as $person)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <img class="h-10 w-10 rounded-full object-cover mr-3 border dark:border-gray-600" src="{{ $person->default_photo_url }}" alt="">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $person->full_name }}
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                            {{ $person->gender?->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                            {{ $person->birth_date?->format('M j, Y') ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('person.show', ['tree' => $tree->id, 'person' => $person->id]) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">View</a>
                                <button wire:click="editPerson({{ $person->id }})" class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300">Edit</button>
                                <button 
                                    wire:click="deletePerson({{ $person->id }})"
                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                >
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                            No people found in this tree.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $people->links() }}
    </div>

    {{-- Modal (Add Person) --}}
    @if($showAddPerson)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 transition-opacity">
            <div class="bg-white dark:bg-gray-800 w-full max-w-lg rounded-xl shadow-2xl p-6 max-h-[90vh] overflow-y-auto transform transition-all scale-100">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Add New Person</h2>
                    <button wire:click="closeAddPerson" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <livewire:tree.actions.add-person-to-tree-form :tree="$tree" />
            </div>
        </div>
    @endif

    {{-- Modal (Edit Person) --}}
    @if($showEditModal && $editingPersonId)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 transition-opacity">
            <div class="bg-white dark:bg-gray-800 w-full max-w-lg rounded-xl shadow-2xl p-6 max-h-[90vh] overflow-y-auto transform transition-all scale-100">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Edit Person</h2>
                    <button wire:click="closeEditModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <livewire:person.actions.edit-person-form :person="\App\Models\Person::find($editingPersonId)" />
            </div>
        </div>
    @endif

    <x-modal.delete-confirmation 
        :show="$showDeleteConfirmation" 
        title="Delete Person?" 
        message="Are you sure you want to delete this person? This action cannot be undone and will remove them from all family trees and relationships."
        onConfirm="confirmDelete"
        onCancel="cancelDelete"
    />
</div>
