<x-layouts.base :title="$title ?? 'Family Tree'">
    <x-slot:head>
        <!-- CropperJS -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    </x-slot:head>

    <!-- Navbar -->
    <x-navbar>
        @if(isset($breadcrumbs))
            <x-slot:breadcrumbs>
                {{ $breadcrumbs }}
            </x-slot:breadcrumbs>
        @endif
    </x-navbar>

    <!-- Page Content -->
    <main class="max-w-5xl mx-auto mt-8 px-4">
        {{ $slot }}
    </main>
</x-layouts.base>
