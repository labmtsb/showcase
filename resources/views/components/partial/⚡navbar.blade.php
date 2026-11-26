<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

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
                            <x-lucide-panel-left-open class="w-6 h-6" />
                        </button>
                    @endauth

                    <livewire:partial.logo />

                </div>
                <div class="flex items-center">

                    <livewire:partial.toggle-theme />

                    @auth
                        <livewire:partial.user-menu />
                    @endauth
                </div>
            </div>
        </div>
    </nav>
</div>
