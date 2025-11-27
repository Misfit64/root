<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-100 dark:bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'Family Tree' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <!-- CropperJS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
</head>
<body class="h-full flex flex-col text-gray-900 dark:text-gray-100 overflow-hidden">

    <!-- Navbar -->
    <header class="bg-white dark:bg-gray-800 shadow z-20 relative">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="/" class="text-xl font-bold text-blue-700 dark:text-blue-400">
                    FamilyTree
                </a>
                {{-- Breadcrumbs Placeholder --}}
                @if(isset($breadcrumbs))
                    <nav class="hidden md:flex items-center text-sm text-gray-500 dark:text-gray-400">
                        <span class="mx-2">/</span>
                        {{ $breadcrumbs }}
                    </nav>
                @endif
            </div>

            <nav>
                <a href="{{ route('tree.index') }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white mr-4">My Trees</a>
                @auth
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Login</a>
                @endauth
            </nav>
        </div>
    </header>

    <!-- Page Content -->
    <main class="flex-grow flex flex-col relative overflow-hidden">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
