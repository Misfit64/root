<div class="w-full">
    <input 
        type="text"
        class="border border-gray-300 dark:border-gray-600 rounded p-2 w-full bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
        placeholder="Search a person..."
        wire:model.live="search"
    />

    @if(strlen($search) > 1)
        <div class="border border-gray-300 dark:border-gray-600 rounded mt-1 bg-white dark:bg-gray-800 shadow-md z-10 absolute w-full max-w-md">
            @forelse($results as $result)
                <div    
                    class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-gray-900 dark:text-white transition"
                    wire:click="selectPerson({{ $result->id }})"
                >
                    {{ $result->full_name }}
                </div>
            @empty
                <div class="p-2 text-gray-500 dark:text-gray-400">No matches found.</div>
            @endforelse
        </div>
    @endif

    @if($value)
        <div class="mt-2 p-2 bg-gray-100 dark:bg-gray-700 rounded text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600">
            Selected: 
            <strong>{{ App\Models\Person::find($value)?->full_name }}</strong>
        </div>
    @endif
</div>
