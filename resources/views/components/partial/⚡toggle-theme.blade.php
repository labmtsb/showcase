<?php

use Livewire\Component;

new class extends Component {
    //
};
?>

<div x-data="{
    darkMode: localStorage.getItem('color-theme') === 'dark',

    openTooltipToggleTheme: false,

    toggleTheme() {
        this.darkMode = !this.darkMode;

        document.documentElement.classList.toggle('dark', this.darkMode);

        localStorage.setItem(
            'color-theme',
            this.darkMode ? 'dark' : 'light'
        );
    }
}" x-init="darkMode = localStorage.getItem('color-theme') === 'dark'">
    <x-tooltip placement="bottom" text="">
        <button type="button" @click="toggleTheme()" @mouseover="openTooltipToggleTheme = true"
            @mouseleave="openTooltipToggleTheme = false"
            class="relative text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-2.5">
            {{-- Moon --}}
            <x-lucide-moon class="w-5 h-5" x-show="!darkMode" x-cloak />

            {{-- Sun --}}
            <x-lucide-sun class="w-5 h-5" x-show="darkMode" x-cloak />
        </button>

        <x-slot:content>
            <span x-text="darkMode ? 'Toggle light mode' : 'Toggle dark mode'"></span>
        </x-slot:content>
    </x-tooltip>

</div>
