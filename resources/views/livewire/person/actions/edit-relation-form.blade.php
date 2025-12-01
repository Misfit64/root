<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Relationship Type</label>
        <select wire:model="subtype"
            class="border border-gray-300 dark:border-gray-600 rounded p-2 w-full bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
            @foreach(App\Enums\RelationshipSubType::cases() as $type)
                @if($relationType === App\Enums\RelationshipType::Spouse)
                    @if(in_array($type, [App\Enums\RelationshipSubType::Married, App\Enums\RelationshipSubType::Separated]))
                        <option value="{{ $type->value }}">{{ $type->name }}</option>
                    @endif
                @else
                    @if(!in_array($type, [App\Enums\RelationshipSubType::Married, App\Enums\RelationshipSubType::Separated]))
                        <option value="{{ $type->value }}">{{ $type->name }}</option>
                    @endif
                @endif
            @endforeach
        </select>
        @error('subtype') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    @if($relationType === App\Enums\RelationshipType::Child && count($potentialOtherParents) > 0)
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Other Parent (Optional)</label>
            <select wire:model="otherParentId"
                class="border border-gray-300 dark:border-gray-600 rounded p-2 w-full bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                <option value="">Select Other Parent</option>
                @foreach($potentialOtherParents as $parent)
                    <option value="{{ $parent['id'] }}">{{ $parent['name'] }}</option>
                @endforeach
            </select>
            @error('otherParentId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
    @endif

    <div class="flex justify-end">
        <button wire:click="save" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Save Changes
        </button>
    </div>
</div>