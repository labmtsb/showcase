<?php

use Livewire\Component;

new class extends Component {
    public $breadcrumb = [];
};
?>

<div>
    @auth
        <nav class="flex my-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" wire:navigate.hover
                        class="inline-flex items-center text-sm font-medium text-body hover:text-fg-brand">

                        <x-lucide-house class="w-4 h-4 me-1.5" />
                        Home
                    </a>
                </li>
                @foreach ($breadcrumb as $key => $value)
                    <li>
                        <div class="flex items-center space-x-1.5">

                            <x-lucide-chevron-right class="w-3.5 h-3.5 rtl:rotate-180 text-body" />

                            <a href="{{ $value['url'] }}" wire:navigate.hover
                                class="inline-flex items-center text-sm font-medium text-body hover:text-fg-brand">{{ $value['name'] }}</a>
                        </div>
                    </li>
                @endforeach

            </ol>
        </nav>
    @endauth
</div>
