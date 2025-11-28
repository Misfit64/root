<div class="max-w-4xl mx-auto py-8" x-data="{ open: false }" @tree-saved.window="open = false">
    
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Family Trees</h1>
        <button wire:click="create" @click="open = true" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
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

    {{-- Shared Trees --}}
    @if($sharedTrees->count() > 0)
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Shared with Me</h2>
            <div class="space-y-4">
                @foreach($sharedTrees as $tree)
                    <div class="bg-white dark:bg-gray-800 p-6 rounded shadow flex justify-between items-center transition hover:shadow-md border-l-4 border-purple-500">
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
                                Owner: {{ $tree->owner?->name ?? 'Unknown' }} • Role: {{ ucfirst($tree->pivot->role) }}
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('tree.show', $tree->id) }}" class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-200 rounded hover:bg-blue-200 dark:hover:bg-blue-800 text-sm transition">
                                View
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Modal --}}
    <div x-show="open" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="open = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4" id="modal-title">
                        {{ $editingTreeId ? 'Edit Tree' : 'Create New Tree' }}
                    </h3>

                    {{-- Tabs --}}
                    <div class="border-b border-gray-200 dark:border-gray-700 mb-4">
                        <nav class="-mb-px flex flex-wrap gap-4">
                            <button wire:click="$set('activeTab', 'details')" class="{{ $activeTab === 'details' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Details
                            </button>
                            @if($editingTreeId)
                                <button wire:click="$set('activeTab', 'members')" class="{{ $activeTab === 'members' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                    Manage Members
                                </button>
                            @endif
                            <button wire:click="$set('activeTab', 'invite')" class="{{ $activeTab === 'invite' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                {{ $editingTreeId ? 'Add Members' : 'Invite People' }}
                            </button>
                            @if($editingTreeId)
                                <button wire:click="$set('activeTab', 'transfer')" class="{{ $activeTab === 'transfer' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                    Transfer Ownership
                                </button>
                            @endif
                        </nav>
                    </div>

                    <div class="mt-4">
                        <form wire:submit="save" class="space-y-4">
                            
                            {{-- Details Tab --}}
                            <div x-show="$wire.activeTab === 'details'">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tree Name</label>
                                    <input type="text" wire:model="name" class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                    <textarea wire:model="description" class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500" rows="2"></textarea>
                                    @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Members Tab (Edit Only) --}}
                            @if($editingTreeId)
                                <div x-show="$wire.activeTab === 'members'">
                                    @if(count($editingTreeMembers) > 0)
                                        <ul class="space-y-3">
                                            @foreach($editingTreeMembers as $member)
                                                <li class="flex justify-between items-center bg-gray-50 dark:bg-gray-700 p-3 rounded">
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $member->name }}</div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $member->email }}</div>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <select 
                                                            wire:change="updateMemberRole({{ $member->id }}, $event.target.value)"
                                                            class="text-xs border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                                                        >
                                                            <option value="viewer" {{ $member->pivot->role === 'viewer' ? 'selected' : '' }}>Viewer</option>
                                                            <option value="editor" {{ $member->pivot->role === 'editor' ? 'selected' : '' }}>Editor</option>
                                                        </select>
                                                        
                                                        <button type="button" wire:click="removeMember({{ $member->id }})" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 p-1">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                        </button>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No other users have access to this tree.</p>
                                    @endif
                                </div>

                            {{-- Transfer Ownership Tab --}}
                                <div x-show="$wire.activeTab === 'transfer'">
                                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded p-4">
                                        <h4 class="text-sm font-bold text-red-800 dark:text-red-300 mb-2">Danger Zone</h4>
                                        <p class="text-sm text-red-700 dark:text-red-400 mb-4">
                                            Transferring ownership will give full control of this tree to another user. You will remain as an Editor, but you will lose the ability to delete the tree or manage its members.
                                        </p>
                                        
                                        @if(count($editingTreeMembers) > 0)
                                            <div class="mb-4">
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select New Owner</label>
                                                <select wire:model.live="selectedNewOwnerId" class="block w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-red-500 focus:border-red-500">
                                                    <option value="">Select a member...</option>
                                                    @foreach($editingTreeMembers as $member)
                                                        <option value="{{ $member->id }}">{{ $member->name }} ({{ $member->email }})</option>
                                                    @endforeach
                                                </select>
                                                @error('selectedNewOwnerId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                            </div>

                                            <button 
                                                type="button" 
                                                wire:click="confirmTransfer"
                                                class="w-full px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                                @if(!$selectedNewOwnerId) disabled @endif
                                            >
                                                Transfer Ownership
                                            </button>
                                        @else
                                            <p class="text-sm text-gray-500 dark:text-gray-400">You must add members to this tree before you can transfer ownership.</p>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            {{-- Invite Tab --}}
                            <div x-show="$wire.activeTab === 'invite'">
                                <div class="space-y-3">
                                    @foreach($invites as $index => $invite)
                                        <div class="flex gap-2 items-start">
                                            <div class="flex-grow">
                                                <input type="email" wire:model.live="invites.{{ $index }}.email" placeholder="user@example.com" class="block w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 text-sm">
                                                @error("invites.{$index}.email") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="w-28">
                                                <select wire:model="invites.{{ $index }}.role" class="block w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 text-sm">
                                                    <option value="viewer">Viewer</option>
                                                    <option value="editor">Editor</option>
                                                </select>
                                            </div>
                                        </div>
                                    @endforeach
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Enter emails to invite. New fields appear automatically.</p>
                                </div>
                            </div>

                            <div class="flex justify-end gap-2 mt-6 pt-4 border-t dark:border-gray-700">
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

    {{-- Transfer Confirmation Modal --}}
    @if($showTransferConfirmation)
    <div class="fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="cancelTransfer"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                Transfer Ownership
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Are you sure you want to transfer ownership of this tree? This action cannot be undone. You will lose administrative control.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="transferOwnership" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Transfer
                    </button>
                    <button type="button" wire:click="cancelTransfer" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
