<div class="max-w-4xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Review Merge</h1>
    
    <div class="grid grid-cols-3 gap-6 mb-8">
        {{-- Headers --}}
        <div class="font-bold text-gray-500 text-right pt-2">Field</div>
        <div class="font-bold text-center bg-blue-50 p-2 rounded">Target (Keep)</div>
        <div class="font-bold text-center bg-red-50 p-2 rounded">Source (Delete)</div>

        {{-- First Name --}}
        <div class="text-right pt-2 font-medium">First Name</div>
        <label class="border p-3 rounded cursor-pointer hover:bg-gray-50 flex items-center gap-2">
            <input type="radio" wire:model="selected.first_name" value="{{ $target->first_name }}">
            <span>{{ $target->first_name }}</span>
        </label>
        <label class="border p-3 rounded cursor-pointer hover:bg-gray-50 flex items-center gap-2">
            <input type="radio" wire:model="selected.first_name" value="{{ $source->first_name }}">
            <span>{{ $source->first_name }}</span>
        </label>

        {{-- Last Name --}}
        <div class="text-right pt-2 font-medium">Last Name</div>
        <label class="border p-3 rounded cursor-pointer hover:bg-gray-50 flex items-center gap-2">
            <input type="radio" wire:model="selected.last_name" value="{{ $target->last_name }}">
            <span>{{ $target->last_name }}</span>
        </label>
        <label class="border p-3 rounded cursor-pointer hover:bg-gray-50 flex items-center gap-2">
            <input type="radio" wire:model="selected.last_name" value="{{ $source->last_name }}">
            <span>{{ $source->last_name }}</span>
        </label>

        {{-- Gender --}}
        <div class="text-right pt-2 font-medium">Gender</div>
        <label class="border p-3 rounded cursor-pointer hover:bg-gray-50 flex items-center gap-2">
            <input type="radio" wire:model="selected.gender" value="{{ $target->gender?->value }}">
            <span>{{ $target->gender?->name ?? 'Unknown' }}</span>
        </label>
        <label class="border p-3 rounded cursor-pointer hover:bg-gray-50 flex items-center gap-2">
            <input type="radio" wire:model="selected.gender" value="{{ $source->gender?->value }}">
            <span>{{ $source->gender?->name ?? 'Unknown' }}</span>
        </label>

        {{-- Birth Date --}}
        <div class="text-right pt-2 font-medium">Birth Date</div>
        <label class="border p-3 rounded cursor-pointer hover:bg-gray-50 flex items-center gap-2">
            <input type="radio" wire:model="selected.birth_date" value="{{ $target->birth_date?->format('Y-m-d') }}">
            <span>{{ $target->birth_date?->format('Y-m-d') ?? 'Unknown' }}</span>
        </label>
        <label class="border p-3 rounded cursor-pointer hover:bg-gray-50 flex items-center gap-2">
            <input type="radio" wire:model="selected.birth_date" value="{{ $source->birth_date?->format('Y-m-d') }}">
            <span>{{ $source->birth_date?->format('Y-m-d') ?? 'Unknown' }}</span>
        </label>

        {{-- Notes --}}
        <div class="text-right pt-2 font-medium">Notes</div>
        <label class="border p-3 rounded cursor-pointer hover:bg-gray-50 flex items-center gap-2">
            <input type="radio" wire:model="selected.notes" value="{{ $target->notes }}">
            <span class="text-sm truncate max-w-xs">{{ $target->notes ?? 'None' }}</span>
        </label>
        <label class="border p-3 rounded cursor-pointer hover:bg-gray-50 flex items-center gap-2">
            <input type="radio" wire:model="selected.notes" value="{{ $source->notes }}">
            <span class="text-sm truncate max-w-xs">{{ $source->notes ?? 'None' }}</span>
        </label>
    </div>

    <div class="bg-yellow-50 border border-yellow-200 p-4 rounded mb-8 text-sm text-yellow-800">
        <strong>Warning:</strong> Relationships from the source person ({{ $source->full_name }}) will be moved to the target person ({{ $target->full_name }}). The source person record will be permanently deleted. This action cannot be undone.
    </div>

    <div class="flex justify-end gap-2">
        <a href="{{ route('person.merge.select', ['tree' => $tree->id, 'person' => $target->id]) }}" class="px-4 py-2 border rounded hover:bg-gray-100">
            Back
        </a>
        <button 
            wire:click="merge" 
            wire:confirm="Are you sure you want to merge these records? The source record will be deleted."
            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
        >
            Confirm Merge
        </button>
    </div>
</div>
