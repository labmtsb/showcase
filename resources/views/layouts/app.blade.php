<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">

    <title>{{ $title }} - {{ config('app.name') }}</title>

    @fonts

    @livewireStyles

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <script data-navigate-once>
        function applyTheme() {
            const isDark =
                localStorage.getItem('color-theme') === 'dark' ||
                (!localStorage.getItem('color-theme') &&
                    window.matchMedia('(prefers-color-scheme: dark').matches);

            document.documentElement.classList.toggle('dark', isDark);
        }

        // Load pertama
        applyTheme();

        // Setiap wire:navigate
        document.addEventListener('livewire:navigated', applyTheme);
    </script>
</head>

<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18]">

    <livewire:partial.navbar />

    <livewire:partial.sidebar />

    @auth
        <div class="p-4 sm:ml-64 mt-14">

            <livewire:partial.breadcrumb :breadcrumb='$breadcrumb ?? []' />

            {{ $slot }}

        </div>

        <x-toast placement="top-right" variant="success" duration="4000" :dismissible="true" :progress="true" :show-icon="true"
            max="5" width="sm" />
    @endauth

    @guest
        <div class="w-full h-screen flex flex-col items-center justify-center gap-4">

            {{ $slot }}

        </div>
    @endguest

    @livewireScripts
</body>

</html>
