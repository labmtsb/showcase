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

                <livewire:partial.logo />

                <ul class="space-y-2 font-medium mt-8">
                    <livewire:partial.navlink />
                </ul>
            </div>
        </aside>
    @endauth
</div>
