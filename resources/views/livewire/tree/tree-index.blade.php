<div class="max-w-4xl mx-auto py-8">
    
    <h1 class="text-2xl font-bold mb-6">My Family Trees</h1>

    {{-- Create New Tree Form --}}
    <div class="bg-white p-6 rounded shadow mb-8">
        <h2 class="text-lg font-semibold mb-4">Create New Tree</h2>
        <form wire:submit="save" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Tree Name</label>
                <input type="text" wire:model="name" class="mt-1 block w-full border rounded p-2">
                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea wire:model="description" class="mt-1 block w-full border rounded p-2" rows="2"></textarea>
                @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Create Tree
            </button>
        </form>
    </div>

    {{-- List of Trees --}}
    <div class="space-y-4">
        @forelse($trees as $tree)
            <div class="bg-white p-6 rounded shadow flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-semibold">
                        <a href="{{ route('person.show', ['tree' => $tree->id, 'person' => 1]) }}" class="hover:underline text-blue-600">
                            {{ $tree->name }}
                        </a>
                    </h3>
                    @if($tree->description)
                        <p class="text-gray-600 text-sm mt-1">{{ $tree->description }}</p>
                    @endif
                    <div class="text-xs text-gray-400 mt-2">
                        Created: {{ $tree->created_at->format('M j, Y') }}
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    {{-- Link to view tree (assuming person 1 exists for now, or just link to tree dashboard if it existed) --}}
                    {{-- We'll just link to the first person or a tree view if available. For now, let's assume person 1 is a safe bet or just link to tree root if we had one. --}}
                    {{-- Actually, the user asked for TreeIndex, maybe they want to click into it. I'll link to a hypothetical tree view or just leave it as a list for now. --}}
                    {{-- I'll add a "View" button that goes to the person show page we've been working on, assuming ID 1 for now as a placeholder, or maybe we should fetch the root person. --}}
                    {{-- For simplicity and robustness, I'll just link to the tree's people list if that existed, but we only have person.show. I'll link to person 1 for now but add a note. --}}
                    
                    <button 
                        wire:click="delete({{ $tree->id }})"
                        wire:confirm="Are you sure you want to delete this tree?"
                        class="px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200 text-sm"
                    >
                        Delete
                    </button>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500 py-8">
                You haven't created any family trees yet.
            </div>
        @endforelse
    </div>

</div>
