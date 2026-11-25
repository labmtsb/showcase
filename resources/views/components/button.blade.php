@props([
    'label' => 'Submit',
    'loadingLabel' => 'Loading...',
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'target' => null,
    'loading' => false,
    'disabled' => false,
    'full' => false,
])

@php
    $variants = [
        'primary' => 'bg-brand hover:bg-brand-strong focus:ring-brand-medium',
        'secondary' => 'bg-gray-600 hover:bg-gray-700 focus:ring-gray-300',
        'success' => 'bg-green-600 hover:bg-green-700 focus:ring-green-300',
        'danger' => 'bg-red-600 hover:bg-red-700 focus:ring-red-300',
        'light' => 'bg-gray-100 text-gray-900 hover:bg-gray-200 focus:ring-gray-300',
    ];

    $sizes = [
        'sm' => 'text-xs px-3 py-2',
        'md' => 'text-sm px-4 py-2.5',
        'lg' => 'text-base px-5 py-3',
    ];

    $variantClass = $variants[$variant] ?? $variants['primary'];
    $sizeClass = $sizes[$size] ?? $sizes['md'];

    $isLoading = $loading || filled($target);
@endphp

<button type="{{ $type }}" @disabled($disabled)
    @if ($target) wire:loading.attr="disabled"
        wire:target="{{ $target }}"
        wire:loading.class="!bg-gray-400 !cursor-not-allowed !opacity-70"
    @elseif ($loading)
        wire:loading.attr="disabled"
        wire:loading.class="!bg-gray-400 !cursor-not-allowed !opacity-70" @endif
    {{ $attributes->class([
        'text-white box-border border border-transparent shadow-xs',
        'font-medium leading-5 rounded-base focus:outline-none focus:ring-4',
        'transition-colors inline-flex items-center justify-center',
        'cursor-not-allowed opacity-60' => $disabled,
        $variantClass,
        $sizeClass,
        'w-full' => $full,
    ]) }}>
    {{-- Loading Spinner --}}
    @if ($isLoading)
        <svg wire:loading @if ($target) wire:target="{{ $target }}" @endif
            class="w-4 h-4 mr-2 animate-spin shrink-0" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />

            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
        </svg>
    @endif

    {{-- Button Label --}}
    @if ($isLoading)
        <span wire:loading.remove @if ($target) wire:target="{{ $target }}" @endif>
            {{ $label }}
        </span>

        <span wire:loading @if ($target) wire:target="{{ $target }}" @endif>
            {{ $loadingLabel }}
        </span>
    @else
        {{ $label }}
    @endif
</button>
