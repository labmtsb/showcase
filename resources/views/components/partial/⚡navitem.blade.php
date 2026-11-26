<?php

use Livewire\Component;

new class extends Component {
    public $route;
    public $label;
    public $icon = 'house';
};
?>


<li>
    <a href="{{ $route }}" wire:navigate.hover wire:current="text-fg-brand"
        class="flex items-center px-2 py-1.5 text-body rounded-base hover:bg-neutral-tertiary hover:text-fg-brand group">

        <x-dynamic-component :component="'lucide-' . $icon" class="w-5 h-5 transition duration-75 group-hover:text-fg-brand" />

        <span class="ms-3">{{ $label }}</span>
    </a>
</li>
