@props([
    'show' => false, 
    'title' => 'Confirmation', 
    'message' => 'Are you sure?', 
    'confirmText' => 'Confirm',
    'confirmColor' => 'bg-red-600 hover:bg-red-700',
    'onConfirm', 
    'onCancel'
])

@if($show)
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[70] p-4">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl max-w-md w-full transform transition-all scale-100">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">{{ $title }}</h3>
            <p class="text-gray-700 dark:text-gray-300 mb-6">
                {{ $message }}
            </p>
            <div class="flex justify-end gap-3">
                <button 
                    wire:click="{{ $onCancel }}" 
                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded hover:bg-gray-300 dark:hover:bg-gray-600 transition"
                >
                    Cancel
                </button>
                <button 
                    wire:click="{{ $onConfirm }}" 
                    class="px-4 py-2 text-white rounded transition shadow {{ $confirmColor }}"
                >
                    {{ $confirmText }}
                </button>
            </div>
        </div>
    </div>
@endif
