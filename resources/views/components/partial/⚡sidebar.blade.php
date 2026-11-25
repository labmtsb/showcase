<?php

use Livewire\Component;

new class extends Component {
    //
};
?>

<div>
    @auth
        <aside x-data="{ open: false }" @toggle-sidebar.window="open = !open"
            class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform sm:translate-x-0"
            :class="open ? 'translate-x-0' : '-translate-x-full'">
            <div class="h-full px-3 py-4 overflow-y-auto bg-neutral-primary-soft border-e border-default">
                <a href="#" class="flex items-center ps-2.5 mb-5">
                    <img src="https://flowbite.com/docs/images/logo.svg" class="h-6 me-3" alt="Flowbite Logo" />
                    <span class="self-center text-lg text-heading font-semibold whitespace-nowrap">Flowbite</span>
                </a>
                <ul class="space-y-2 font-medium">
                    <li>
                        <a href="{{ route('home') }}" wire:navigate.hover wire:current="text-fg-brand"
                            class="flex items-center px-2 py-1.5 text-body rounded-base hover:bg-neutral-tertiary hover:text-fg-brand group">
                            <svg class="w-5 h-5 transition duration-75 group-hover:text-fg-brand" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="m4 12 8-8 8 8M6 10.5V19a1 1 0 0 0 1 1h3v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h3a1 1 0 0 0 1-1v-8.5" />
                            </svg>


                            <span class="ms-3">Home</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('projects') }}" wire:navigate.hover wire:current="text-fg-brand"
                            class="flex items-center px-2 py-1.5 text-body rounded-base hover:bg-neutral-tertiary hover:text-fg-brand group">


                            <svg class="w-5 h-5 transition duration-75 group-hover:text-fg-brand"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7H5a2 2 0 0 0-2 2v4m5-6h8M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m0 0h3a2 2 0 0 1 2 2v4m0 0v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-6m18 0s-4 2-9 2-9-2-9-2m9-2h.01" />
                            </svg>

                            <span class="ms-3">Projects</span>
                        </a>
                    </li>
                </ul>
            </div>
        </aside>
    @endauth
</div>
