<div class="max-w-4xl mx-auto py-8">
    
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">{{ $tree->name }}</h1>
            @if($tree->description)
                <p class="text-gray-600 mt-1">{{ $tree->description }}</p>
            @endif
        </div>
        
        {{-- Add New Person Button --}}
        <button wire:click="openAddPerson" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
            Add New Person
        </button>
    </div>

    {{-- Search Bar --}}
    <div class="mb-6">
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search" 
            placeholder="Search people..." 
            class="w-full border rounded p-2"
        >
    </div>

    {{-- People List --}}
    <div class="bg-white rounded shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gender</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Birth Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($people as $person)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                @if($person->photo_path)
                                    <img class="h-8 w-8 rounded-full object-cover mr-3" src="{{ asset($person->photo_path) }}" alt="">
                                @else
                                    <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center mr-3 text-xs text-gray-500">
                                        {{ substr($person->first_name, 0, 1) }}
                                    </div>
                                @endif
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $person->full_name }}
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $person->gender?->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $person->birth_date?->format('M j, Y') ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('person.show', ['tree' => $tree->id, 'person' => $person->id]) }}" class="text-blue-600 hover:text-blue-900">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
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
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">

            <div class="bg-white w-full max-w-lg rounded shadow-lg p-4 max-h-[90vh] overflow-y-auto">

                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold">Add New Person</h2>
                    <button wire:click="closeAddPerson" class="text-gray-500 hover:text-black">✕</button>
                </div>

                <livewire:tree.actions.add-person-to-tree-form 
                    :tree="$tree"
                />

            </div>

        </div>
    @endif

</div>
