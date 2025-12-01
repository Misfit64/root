@props(['title' => config('app.name', 'Laravel'), 'htmlClass' => 'h-full bg-gray-100 dark:bg-gray-900', 'bodyClass' => 'font-sans antialiased h-full text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-900'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $htmlClass }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <!-- Dark Mode Script -->
    <script>
        function applyTheme() {
            if (localStorage.getItem('darkMode') === 'true' || (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }

        // Apply on initial load
        applyTheme();

        // Apply on Livewire navigation
        document.addEventListener('livewire:navigated', applyTheme);
    </script>

    {{ $head ?? '' }}
</head>

<body class="{{ $bodyClass }}">
    {{ $slot }}

    @livewireScripts
</body>

</html>