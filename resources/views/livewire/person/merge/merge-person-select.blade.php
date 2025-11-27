<div class="max-w-2xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">Merge Person</h1>
    <p class="mb-6 text-gray-600 dark:text-gray-400">
        Select the duplicate record you want to merge into <strong>{{ $person->full_name }}</strong>.
        The selected record will be deleted after the merge.
    </p>

    <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search Duplicate Person</label>
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search" 
            class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
            placeholder="Type name..."
        >
    </div>

    @if(!empty($results))
        <div class="border border-gray-300 dark:border-gray-600 rounded divide-y divide-gray-200 dark:divide-gray-700 mb-6 bg-white dark:bg-gray-800">
            @foreach($results as $result)
                <div 
                    class="p-3 cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/50 flex justify-between items-center transition {{ $selectedPersonId == $result->id ? 'bg-blue-100 dark:bg-blue-900 border-l-4 border-blue-500' : '' }}"
                    wire:click="selectPerson({{ $result->id }})"
                >
                    <div>
                        <div class="font-bold text-gray-900 dark:text-white">{{ $result->full_name }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $result->gender?->name }} • Born: {{ $result->birth_date?->format('Y') ?? '?' }}
                        </div>
                    </div>
                    @if($selectedPersonId == $result->id)
                        <span class="text-blue-600 dark:text-blue-400 font-bold">Selected</span>
                    @endif
                </div>
            @endforeach
        </div>
    @elseif(strlen($search) > 1)
        <div class="text-gray-500 dark:text-gray-400 mb-6">No matches found.</div>
    @endif

    <div class="flex justify-end gap-2">
        <a href="{{ route('person.show', ['tree' => $tree->id, 'person' => $person->id]) }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition">
            Cancel
        </a>
        <button 
            wire:click="next" 
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 transition"
            @if(!$selectedPersonId) disabled @endif
        >
            Review Merge &rarr;
        </button>
    </div>
</div>
