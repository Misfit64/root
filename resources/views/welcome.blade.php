<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-100 dark:bg-gray-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-gray-900 dark:text-gray-100">
    <div class="min-h-screen flex flex-col">
        <!-- Navbar -->
        <header class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <a href="/" class="text-xl font-bold text-blue-700 dark:text-blue-400">
                        FamilyTree
                    </a>
                </div>

                <nav>
                    @auth
                        <a href="{{ route('tree.index') }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white mr-4">My Trees</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white mr-4">Login</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Register</a>
                        @endif
                    @endauth
                </nav>
            </div>
        </header>

        <!-- Hero Content -->
        <main class="flex-grow flex items-center justify-center">
            <div class="text-center px-4">
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Visualize Your Family History
                </h1>
                <p class="text-xl text-gray-600 dark:text-gray-400 mb-8 max-w-2xl mx-auto">
                    Create, manage, and explore your family tree with our intuitive and beautiful visualization tools.
                </p>
                <div class="flex justify-center gap-4">
                    @auth
                        <a href="{{ route('tree.index') }}" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                            Go to My Trees
                        </a>
                    @else
                        <a href="{{ route('demo') }}" class="px-6 py-3 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition font-semibold">
                            View Demo
                        </a>
                        <a href="{{ route('login') }}" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                            Get Started
                        </a>
                        <a href="{{ route('register') }}" class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition font-semibold">
                            Sign Up
                        </a>
                    @endauth
                </div>
            </div>
        </main>

        <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 py-8">
            <div class="max-w-7xl mx-auto px-4 text-center text-gray-500 dark:text-gray-400">
                &copy; {{ date('Y') }} FamilyTree. All rights reserved.
            </div>
        </footer>
    </div>
</body>
</html>
