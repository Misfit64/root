<div class="max-w-2xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-4">Merge Person</h1>
    <p class="mb-6 text-gray-600">
        Select the duplicate record you want to merge into <strong>{{ $person->full_name }}</strong>.
        The selected record will be deleted after the merge.
    </p>

    <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-1">Search Duplicate Person</label>
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search" 
            class="w-full border rounded p-2"
            placeholder="Type name..."
        >
    </div>

    @if(!empty($results))
        <div class="border rounded divide-y mb-6">
            @foreach($results as $result)
                <div 
                    class="p-3 cursor-pointer hover:bg-blue-50 flex justify-between items-center {{ $selectedPersonId == $result->id ? 'bg-blue-100 border-l-4 border-blue-500' : '' }}"
                    wire:click="selectPerson({{ $result->id }})"
                >
                    <div>
                        <div class="font-bold">{{ $result->full_name }}</div>
                        <div class="text-sm text-gray-500">
                            {{ $result->gender?->name }} • Born: {{ $result->birth_date?->format('Y') ?? '?' }}
                        </div>
                    </div>
                    @if($selectedPersonId == $result->id)
                        <span class="text-blue-600 font-bold">Selected</span>
                    @endif
                </div>
            @endforeach
        </div>
    @elseif(strlen($search) > 1)
        <div class="text-gray-500 mb-6">No matches found.</div>
    @endif

    <div class="flex justify-end gap-2">
        <a href="{{ route('person.show', ['tree' => $tree->id, 'person' => $person->id]) }}" class="px-4 py-2 border rounded hover:bg-gray-100">
            Cancel
        </a>
        <button 
            wire:click="next" 
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
            @if(!$selectedPersonId) disabled @endif
        >
            Review Merge &rarr;
        </button>
    </div>
</div>
