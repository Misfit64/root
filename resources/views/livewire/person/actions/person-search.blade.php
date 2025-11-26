<div class="w-full">
    <input 
        type="text"
        class="border rounded p-2 w-full"
        placeholder="Search a person..."
        wire:model="search"
    />

    @if(strlen($search) > 1)
        <div class="border rounded mt-1 bg-white shadow-md">
            @forelse($results as $result)
                <div 
                    class="p-2 hover:bg-gray-100 cursor-pointer"
                    wire:click="selectPerson({{ $result->id }})"
                >
                    {{ $result->full_name }}
                </div>
            @empty
                <div class="p-2 text-gray-500">No matches found.</div>
            @endforelse
        </div>
    @endif

    @if($selectedPerson)
        <div class="mt-2 p-2 bg-gray-100 rounded">
            Selected: 
            <strong>{{ App\Models\Person::find($selectedPerson)?->full_name }}</strong>
        </div>
    @endif
</div>
