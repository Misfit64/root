<x-layouts.base :title="'FamilyTree'" :bodyClass="'font-sans text-gray-900 antialiased'">
    <div class="min-h-screen flex flex-col justify-start items-center pt-20 bg-gray-100 dark:bg-gray-900">
        <div>
            <a href="/" class="text-3xl font-bold text-blue-700 dark:text-blue-400" wire:navigate>
                FamilyTree
            </a>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
            {{ $slot }}
        </div>
    </div>
</x-layouts.base>
