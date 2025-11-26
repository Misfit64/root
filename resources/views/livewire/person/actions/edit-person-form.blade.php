<div class="p-4 border rounded bg-white space-y-4">

    <h2 class="text-lg font-bold">Edit Person</h2>

    {{-- Name Fields --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
            <input type="text" wire:model="first_name" class="border rounded p-2 w-full">
            @error('first_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
            <input type="text" wire:model="last_name" class="border rounded p-2 w-full">
            @error('last_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Gender --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
        <select wire:model="gender" class="border rounded p-2 w-full">
            <option value="">Select Gender</option>
            @foreach(App\Enums\Gender::cases() as $g)
                <option value="{{ $g->value }}">{{ $g->name }}</option>
            @endforeach
        </select>
        @error('gender') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- Dates --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Birth Date</label>
            <input type="date" wire:model="birth_date" class="border rounded p-2 w-full">
            @error('birth_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Death Date</label>
            <input type="date" wire:model="death_date" class="border rounded p-2 w-full">
            @error('death_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Notes --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
        <textarea wire:model="notes" class="border rounded p-2 w-full" rows="3"></textarea>
        @error('notes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- Photo --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Photo</label>
        <input type="file" wire:model="photo" class="border rounded p-2 w-full">
        @if ($photo)
            <div class="mt-2">
                <img src="{{ $photo->temporaryUrl() }}" class="w-20 h-20 object-cover rounded">
            </div>
        @elseif ($person->photo_path)
             <div class="mt-2">
                <img src="{{ asset($person->photo_path) }}" class="w-20 h-20 object-cover rounded">
            </div>
        @endif
        @error('photo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    {{-- Submit button --}}
    <button
        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        wire:click="save"
    >
        Save Changes
    </button>

</div>
