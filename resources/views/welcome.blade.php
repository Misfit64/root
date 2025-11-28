<x-layouts.base>
    <div class="min-h-screen flex flex-col">
        <!-- Navbar -->
        <x-navbar />

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
                        <a href="{{ route('register') }}" class="px-6 py-3 bg-green-600 dark:bg-green-700 text-white dark:text-white rounded-lg hover:bg-green-700 dark:hover:bg-green-600 transition font-semibold">
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
</x-layouts.base>
