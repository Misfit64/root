<div class="p-4 border rounded bg-white dark:bg-gray-800 dark:border-gray-700 space-y-4">

    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Add New Person</h2>

    {{-- Name Fields --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">First Name</label>
            <input type="text" wire:model="first_name" class="border border-gray-300 dark:border-gray-600 rounded p-2 w-full bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
            @error('first_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name</label>
            <input type="text" wire:model="last_name" class="border border-gray-300 dark:border-gray-600 rounded p-2 w-full bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
            @error('last_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Gender --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Gender</label>
        <select wire:model="gender" class="border border-gray-300 dark:border-gray-600 rounded p-2 w-full bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
            <option value="">Select Gender</option>
            @foreach(App\Enums\Gender::cases() as $g)
                <option value="{{ $g->value }}">{{ $g->name }}</option>
            @endforeach
        </select>
        @error('gender') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- Birth Date --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Birth Date</label>
        <input type="date" wire:model="birth_date" class="border border-gray-300 dark:border-gray-600 rounded p-2 w-full bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
        @error('birth_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- Notes --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
        <textarea wire:model="notes" class="border border-gray-300 dark:border-gray-600 rounded p-2 w-full bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500" rows="3"></textarea>
        @error('notes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- Photo --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Photo</label>
        <input type="file" wire:model="photo" class="border border-gray-300 dark:border-gray-600 rounded p-2 w-full bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
        @if ($photo)
            <div class="mt-2">
                <img src="{{ $photo->temporaryUrl() }}" class="w-20 h-20 object-cover rounded border dark:border-gray-600">
            </div>
        @endif
        @error('photo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- Submit button --}}
    <button
        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition"
        wire:click="save"
    >
        Add Person
    </button>

</div>
