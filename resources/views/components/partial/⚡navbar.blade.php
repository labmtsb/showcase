<?php

use Livewire\Component;

new class extends Component {
    //
};
?>

<div>
    <nav class="fixed top-0 z-50 w-full bg-neutral-primary-soft border-b border-default">
        <div class="px-3 py-3 lg:px-5 lg:pl-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center justify-start rtl:justify-end" x-data="{}">
                    @auth
                        <button @click="$dispatch('toggle-sidebar')"
                            class="sm:hidden text-heading bg-transparent box-border border border-transparent hover:bg-neutral-secondary-medium focus:ring-4 focus:ring-neutral-tertiary font-medium leading-5 rounded-base text-sm p-2 focus:outline-none cursor-pointer">
                            <span class="sr-only">Open sidebar</span>
                            <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                                height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-width="2"
                                    d="M5 7h14M5 12h14M5 17h10" />
                            </svg>
                        </button>
                    @endauth

                    <a href="#" class="flex ms-2 md:me-24">
                        <img src="https://flowbite.com/docs/images/logo.svg" class="h-6 me-3" alt="FlowBite Logo" />
                        <span
                            class="self-center text-lg font-semibold whitespace-nowrap dark:text-white">Showcase</span>
                    </a>
                </div>
                <div class="flex items-center">

                    <div x-data="{
                        darkMode: localStorage.getItem('color-theme') === 'dark',
                    
                        toggleTheme() {
                            this.darkMode = !this.darkMode;
                    
                            document.documentElement.classList.toggle('dark', this.darkMode);
                    
                            localStorage.setItem(
                                'color-theme',
                                this.darkMode ? 'dark' : 'light'
                            );
                        }
                    }" x-init="darkMode = localStorage.getItem('color-theme') === 'dark'">
                        <button type="button" @click="toggleTheme()"
                            class="text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-2.5">
                            <!-- Moon -->
                            <svg x-show="!darkMode" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                            </svg>

                            <!-- Sun -->
                            <svg x-show="darkMode" x-cloak class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                                    fill-rule="evenodd" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>

                    @auth
                        <div class="flex items-center ms-3" x-data="{ openUserMenu: false }">
                            <div class="relative">
                                <button type="button"
                                    class="flex text-sm bg-gray-800 rounded-full focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600"
                                    @click="openUserMenu = true">
                                    <span class="sr-only">Open user menu</span>
                                    <img class="w-8 h-8 rounded-full"
                                        src="https://flowbite.com/docs/images/people/profile-picture-5.jpg"
                                        alt="user photo">
                                </button>


                                <div x-show="openUserMenu" @click.outside="openUserMenu = false" x-transition x-cloak
                                    class="z-50 bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-44 absolute top-12 right-0 mt-2">
                                    <div class="px-4 py-3 border-b border-default-medium" role="none">
                                        <p class="text-sm font-medium text-heading" role="none">
                                            {{ Auth::user()->name }}
                                        </p>
                                        <p class="text-sm text-body truncate" role="none">
                                            {{ Auth::user()->email }}
                                        </p>
                                    </div>
                                    <ul class="p-2 text-sm text-body font-medium" role="none" x-data="{ openLogoutModal: false }">
                                        <li>
                                            <button @click="openLogoutModal = true"
                                                class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded"
                                                role="menuitem">Sign out</button>
                                        </li>

                                        {{-- Log Out Modal  --}}
                                        <div x-show="openLogoutModal" x-transition x-cloak
                                            class="overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 flex justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-black/30">
                                            <div class="relative p-4 w-full max-w-2xl max-h-full">
                                                <!-- Modal content -->
                                                <div @click.outside="openLogoutModal = false"
                                                    class="relative bg-neutral-primary-soft border border-default rounded-base shadow-sm p-4 md:p-6">
                                                    <!-- Modal header -->
                                                    <div
                                                        class="flex items-center justify-between border-b border-default pb-4 md:pb-5">
                                                        <h3 class="text-lg font-medium text-heading">
                                                            Sign out form account
                                                        </h3>
                                                        <button type="button" @click="openLogoutModal = false"
                                                            class="text-body bg-transparent hover:bg-neutral-tertiary hover:text-heading rounded-base text-sm w-9 h-9 ms-auto inline-flex justify-center items-center">
                                                            <svg class="w-5 h-5" aria-hidden="true"
                                                                xmlns="http://www.w3.org/2000/svg" width="24"
                                                                height="24" fill="none" viewBox="0 0 24 24">
                                                                <path stroke="currentColor" stroke-linecap="round"
                                                                    stroke-linejoin="round" stroke-width="2"
                                                                    d="M6 18 17.94 6M18 18 6.06 6" />
                                                            </svg>
                                                            <span class="sr-only">Close modal</span>
                                                        </button>
                                                    </div>

                                                    <!-- Modal body -->
                                                    <div class="space-y-4 md:space-y-6 py-4 md:py-6">
                                                        <p class="leading-relaxed text-body">
                                                            Sign out from yout account.
                                                        </p>

                                                    </div>

                                                    <!-- Modal footer -->
                                                    <div
                                                        class="flex items-center border-t border-default space-x-4 pt-4 md:pt-5">
                                                        <a href="{{ route('logout') }}"
                                                            class="text-white bg-danger box-border border border-transparent hover:bg-danger-strong focus:ring-4 focus:ring-danger-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none">Sign
                                                            Out</a>
                                                        <button @click="openLogoutModal = false"
                                                            class="text-body bg-neutral-secondary-medium box-border border border-default-medium hover:bg-neutral-tertiary-medium hover:text-heading focus:ring-4 focus:ring-neutral-tertiary shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none">Cancel</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
</div>
