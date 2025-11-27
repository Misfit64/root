<div class="max-w-4xl mx-auto py-8" x-data="{ open: false }" @tree-saved.window="open = false">
    
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Family Trees</h1>
        <button @click="open = true; $wire.set('editingTreeId', null); $wire.set('name', ''); $wire.set('description', '');" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
            Create New Tree
        </button>
    </div>

    {{-- List of Trees --}}
    <div class="space-y-4 mb-8">
        @forelse($trees as $tree)
            <div class="bg-white dark:bg-gray-800 p-6 rounded shadow flex justify-between items-center transition hover:shadow-md">
                <div>
                    <h3 class="text-xl font-semibold">
                        <a href="{{ route('tree.show', $tree->id) }}" class="hover:underline text-blue-600 dark:text-blue-400">
                            {{ $tree->name }}
                        </a>
                    </h3>
                    @if($tree->description)
                        <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">{{ $tree->description }}</p>
                    @endif
                    <div class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                        Created: {{ $tree->created_at->format('M j, Y') }}
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('tree.show', $tree->id) }}" class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-200 rounded hover:bg-blue-200 dark:hover:bg-blue-800 text-sm transition">
                        View
                    </a>
                    <button 
                        wire:click="edit({{ $tree->id }})"
                        @click="open = true"
                        class="px-3 py-1 bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-200 rounded hover:bg-yellow-200 dark:hover:bg-yellow-800 text-sm transition"
                    >
                        Edit
                    </button>
                    <button 
                        wire:click="delete({{ $tree->id }})"
                        wire:confirm="Are you sure you want to delete this tree?"
                        class="px-3 py-1 bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-200 rounded hover:bg-red-200 dark:hover:bg-red-800 text-sm transition"
                    >
                        Delete
                    </button>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500 dark:text-gray-400 py-8 bg-white dark:bg-gray-800 rounded shadow">
                You haven't created any family trees yet.
            </div>
        @endforelse
    </div>

    {{-- Modal --}}
    <div x-show="open" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="open = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                        {{ $editingTreeId ? 'Edit Tree' : 'Create New Tree' }}
                    </h3>
                    <div class="mt-4">
                        <form wire:submit="save" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tree Name</label>
                                <input type="text" wire:model="name" class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                <textarea wire:model="description" class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500" rows="2"></textarea>
                                @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex justify-end gap-2 mt-4">
                                <button type="button" @click="open = false" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                                    Cancel
                                </button>
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                                    {{ $editingTreeId ? 'Update Tree' : 'Create Tree' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
