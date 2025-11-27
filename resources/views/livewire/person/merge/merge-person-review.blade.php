<div class="max-w-4xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">Review Merge</h1>
    
    <div class="grid grid-cols-3 gap-6 mb-8">
        {{-- Headers --}}
        <div class="font-bold text-gray-500 dark:text-gray-400 text-right pt-2">Field</div>
        <div class="font-bold text-center bg-blue-50 dark:bg-blue-900/30 text-gray-900 dark:text-white p-2 rounded">Target (Keep)</div>
        <div class="font-bold text-center bg-red-50 dark:bg-red-900/30 text-gray-900 dark:text-white p-2 rounded">Source (Delete)</div>

        {{-- First Name --}}
        <div class="text-right pt-2 font-medium text-gray-700 dark:text-gray-300">First Name</div>
        <label class="border border-gray-300 dark:border-gray-600 p-3 rounded cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2 text-gray-900 dark:text-white transition">
            <input type="radio" wire:model="selected.first_name" value="{{ $target->first_name }}" class="text-blue-600 focus:ring-blue-500">
            <span>{{ $target->first_name }}</span>
        </label>
        <label class="border border-gray-300 dark:border-gray-600 p-3 rounded cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2 text-gray-900 dark:text-white transition">
            <input type="radio" wire:model="selected.first_name" value="{{ $source->first_name }}" class="text-blue-600 focus:ring-blue-500">
            <span>{{ $source->first_name }}</span>
        </label>

        {{-- Last Name --}}
        <div class="text-right pt-2 font-medium text-gray-700 dark:text-gray-300">Last Name</div>
        <label class="border border-gray-300 dark:border-gray-600 p-3 rounded cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2 text-gray-900 dark:text-white transition">
            <input type="radio" wire:model="selected.last_name" value="{{ $target->last_name }}" class="text-blue-600 focus:ring-blue-500">
            <span>{{ $target->last_name }}</span>
        </label>
        <label class="border border-gray-300 dark:border-gray-600 p-3 rounded cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2 text-gray-900 dark:text-white transition">
            <input type="radio" wire:model="selected.last_name" value="{{ $source->last_name }}" class="text-blue-600 focus:ring-blue-500">
            <span>{{ $source->last_name }}</span>
        </label>

        {{-- Gender --}}
        <div class="text-right pt-2 font-medium text-gray-700 dark:text-gray-300">Gender</div>
        <label class="border border-gray-300 dark:border-gray-600 p-3 rounded cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2 text-gray-900 dark:text-white transition">
            <input type="radio" wire:model="selected.gender" value="{{ $target->gender?->value }}" class="text-blue-600 focus:ring-blue-500">
            <span>{{ $target->gender?->name ?? 'Unknown' }}</span>
        </label>
        <label class="border border-gray-300 dark:border-gray-600 p-3 rounded cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2 text-gray-900 dark:text-white transition">
            <input type="radio" wire:model="selected.gender" value="{{ $source->gender?->value }}" class="text-blue-600 focus:ring-blue-500">
            <span>{{ $source->gender?->name ?? 'Unknown' }}</span>
        </label>

        {{-- Birth Date --}}
        <div class="text-right pt-2 font-medium text-gray-700 dark:text-gray-300">Birth Date</div>
        <label class="border border-gray-300 dark:border-gray-600 p-3 rounded cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2 text-gray-900 dark:text-white transition">
            <input type="radio" wire:model="selected.birth_date" value="{{ $target->birth_date?->format('Y-m-d') }}" class="text-blue-600 focus:ring-blue-500">
            <span>{{ $target->birth_date?->format('Y-m-d') ?? 'Unknown' }}</span>
        </label>
        <label class="border border-gray-300 dark:border-gray-600 p-3 rounded cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2 text-gray-900 dark:text-white transition">
            <input type="radio" wire:model="selected.birth_date" value="{{ $source->birth_date?->format('Y-m-d') }}" class="text-blue-600 focus:ring-blue-500">
            <span>{{ $source->birth_date?->format('Y-m-d') ?? 'Unknown' }}</span>
        </label>

        {{-- Notes --}}
        <div class="text-right pt-2 font-medium text-gray-700 dark:text-gray-300">Notes</div>
        <label class="border border-gray-300 dark:border-gray-600 p-3 rounded cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2 text-gray-900 dark:text-white transition">
            <input type="radio" wire:model="selected.notes" value="{{ $target->notes }}" class="text-blue-600 focus:ring-blue-500">
            <span class="text-sm truncate max-w-xs">{{ $target->notes ?? 'None' }}</span>
        </label>
        <label class="border border-gray-300 dark:border-gray-600 p-3 rounded cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2 text-gray-900 dark:text-white transition">
            <input type="radio" wire:model="selected.notes" value="{{ $source->notes }}" class="text-blue-600 focus:ring-blue-500">
            <span class="text-sm truncate max-w-xs">{{ $source->notes ?? 'None' }}</span>
        </label>
    </div>

    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 p-4 rounded mb-8 text-sm text-yellow-800 dark:text-yellow-200">
        <strong>Warning:</strong> Relationships from the source person ({{ $source->full_name }}) will be moved to the target person ({{ $target->full_name }}). The source person record will be permanently deleted. This action cannot be undone.
    </div>

    <div class="flex justify-end gap-2">
        <a href="{{ route('person.merge.select', ['tree' => $tree->id, 'person' => $target->id]) }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition">
            Back
        </a>
        <button 
            wire:click="merge" 
            wire:confirm="Are you sure you want to merge these records? The source record will be deleted."
            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition"
        >
            Confirm Merge
        </button>
    </div>
</div>
