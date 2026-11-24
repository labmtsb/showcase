<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@include('layouts._head', ['title' => $title ?? ''])

<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18]">

    @include('layouts._navbar')

    @include('layouts._sidebar')

    <div class="p-4 sm:ml-64 mt-14">

        @include('layouts._breadcrumb', ['breadcrumb' => $breadcrumb ?? []])

        {{ $slot }}
    </div>

    @livewireScripts
</body>

</html>
