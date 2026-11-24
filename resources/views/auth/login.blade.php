<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">

    <title>{{ config('app.name', 'Showcase') }}</title>

    @fonts

    @livewireStyles

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18]">

    <nav class="fixed top-0 z-50 w-full bg-neutral-primary-soft border-b border-default">
        <div class="px-3 py-3 lg:px-5 lg:pl-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center justify-start rtl:justify-end">

                    <a href="#" class="flex ms-2 md:me-24">
                        <img src="https://flowbite.com/docs/images/logo.svg" class="h-6 me-3" alt="FlowBite Logo" />
                        <span
                            class="self-center text-lg font-semibold whitespace-nowrap dark:text-white">Flowbite</span>
                    </a>
                </div>
                <div class="flex items-center">
                    <button id="theme-toggle" type="button"
                        class="text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-2.5">
                        <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                        <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                                fill-rule="evenodd" clip-rule="evenodd"></path>
                        </svg>
                    </button>

                </div>
            </div>
        </div>
    </nav>


    <div class="w-full h-screen flex flex-col items-center justify-center gap-4">

        <form class="bg-neutral-primary-soft block w-100 p-6 border border-default rounded-base shadow-xs"
            action="{{ route('attempt_login') }}" method="POST" x-data="{ submitting: false }" @submit="submitting = true">
            @if (session('response'))
                <div class="p-4 mb-4 text-sm text-fg-danger-strong rounded-base bg-danger-soft" role="alert">
                    <span class="font-medium">Oops!</span> {{ session('response') }}
                </div>
            @endif

            @csrf


            <div class="mb-5">
                <label for="email"
                    class="block mb-2.5 text-sm font-medium {{ $errors->has('email') ? 'text-fg-danger-strong' : 'text-heading' }}">
                    Your email
                </label>

                <input type="email" id="email" name="email" required
                    class="text-sm rounded-base block w-full px-3 py-2.5 shadow-xs {{ $errors->has('email')
                        ? 'bg-danger-soft border border-danger-subtle text-fg-danger-strong placeholder:text-fg-danger-strong focus:ring-danger focus:border-danger'
                        : 'bg-neutral-secondary-medium border border-default-medium text-heading placeholder:text-body focus:ring-brand focus:border-brand' }}"
                    placeholder="id@lab.net" value="{{ old('email') }}" />

                @error('email')
                    <p class="mt-2.5 text-sm
                    text-fg-danger-strong">
                        {{ $message }}
                    </p>
                @enderror
            </div>



            <div class="mb-5">
                <label for="password"
                    class="block mb-2.5 text-sm font-medium {{ $errors->has('password') ? 'text-fg-danger-strong' : 'text-heading' }}">
                    Your password
                </label>

                <input type="password" id="password" name="password" required
                    class="text-sm rounded-base block w-full px-3 py-2.5 shadow-xs {{ $errors->has('password')
                        ? 'bg-danger-soft border border-danger-subtle text-fg-danger-strong placeholder:text-fg-danger-strong focus:ring-danger focus:border-danger'
                        : 'bg-neutral-secondary-medium border border-default-medium text-heading placeholder:text-body focus:ring-brand focus:border-brand' }}" />

                @error('password')
                    <p class="mt-2.5 text-sm text-fg-danger-strong">
                        {{ $message }}
                    </p>
                @enderror
            </div>


            <label for="remember" class="flex items-center mb-5">
                <input id="remember" type="checkbox" value="" name="remember"
                    class="w-4 h-4 border border-default-medium rounded-xs bg-neutral-secondary-medium focus:ring-2 focus:ring-brand-soft" />
                <p class="ms-2 text-sm font-medium text-heading select-none">Remember me</p>
            </label>

            <button type="submit" :disabled="submitting"
                class="text-white box-border border border-transparent focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none"
                :class="submitting
                    ?
                    'bg-gray-400 cursor-not-allowed' :
                    'bg-brand hover:bg-brand-strong'">
                <svg x-show="submitting" class="w-4 h-4 mr-2 animate-spin inline" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>

                <span x-text="submitting ? 'Signing in...' : 'Sign in'"></span>
            </button>
        </form>
    </div>

    <script src="/static/theme.js"></script>

    @livewireScripts
</body>

</html>
