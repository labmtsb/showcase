<?php

use Livewire\Component;

new class extends Component {
    public $label = 'Submit';
    public $loading_label = 'Loading...';
    public $type = 'submit';
    public $target = null;
    public $state = 'submitting';
};
?>

<div>
    <button type="{{ $type }}" :disabled="{{ $state }}"
        {{ $attributes->merge([
            'class' =>
                'text-white box-border border border-transparent focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none',
        ]) }}
        :class="{{ $state }}
            ?
            'bg-gray-400 cursor-not-allowed' :
            'bg-brand hover:bg-brand-strong'">
        <svg x-show="{{ $state }}" class="w-4 h-4 mr-2 animate-spin inline" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
        </svg>

        <span x-text="{{ $state }} ? '{{ $loading_label }}' : '{{ $label }}'">
        </span>
    </button>
</div>

{{-- <div>
    <button type="{{ $type }}" wire:loading.attr="disabled"
        @if ($target) wire:target="{{ $target }}" @endif
        {{ $attributes->merge([
            'class' =>
                'text-white box-border border border-transparent focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none bg-brand hover:bg-brand-strong disabled:bg-gray-400 disabled:cursor-not-allowed',
        ]) }}>
        <svg wire:loading @if ($target) wire:target="{{ $target }}" @endif
            class="w-4 h-4 mr-2 animate-spin inline" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />

            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
        </svg>

        <span wire:loading.remove @if ($target) wire:target="{{ $target }}" @endif>
            {{ $label }}
        </span>

        <span wire:loading @if ($target) wire:target="{{ $target }}" @endif>
            {{ $loading_label }}
        </span>
    </button>
</div> --}}
