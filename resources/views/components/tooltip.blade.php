@props([
    'text' => null,

    // Placement: top, bottom, left, right
    'placement' => 'bottom',

    // Appearance
    'color' => 'dark',
    'size' => 'sm',

    // Behavior
    'trigger' => 'hover',
    'offset' => 8,

    // Width
    'width' => 'w-fit whitespace-nowrap max-w-50',
])

@php
    $placements = [
        'top' => [
            'tooltip' => 'bottom-full left-1/2 -translate-x-1/2',
            'arrow' => 'top-full left-1/2 -translate-x-1/2 -translate-y-1/2',
        ],
        'bottom' => [
            'tooltip' => 'top-full left-1/2 -translate-x-1/2',
            'arrow' => 'bottom-full left-1/2 -translate-x-1/2 translate-y-1/2',
        ],
        'left' => [
            'tooltip' => 'right-full top-1/2 -translate-y-1/2',
            'arrow' => 'left-full top-1/2 -translate-y-1/2 -translate-x-1/2',
        ],
        'right' => [
            'tooltip' => 'left-full top-1/2 -translate-y-1/2',
            'arrow' => 'right-full top-1/2 -translate-y-1/2 translate-x-1/2',
        ],
    ];

    $colors = [
        'dark' => 'bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900',
        'light' =>
            'bg-white text-gray-900 border border-gray-200 dark:bg-gray-800 dark:text-white dark:border-gray-700',
        'primary' => 'bg-purple-600 text-white',
        'success' => 'bg-green-600 text-white',
        'danger' => 'bg-red-600 text-white',
    ];

    $sizes = [
        'xs' => 'text-xs px-2 py-1',
        'sm' => 'text-sm px-3 py-2',
        'md' => 'text-base px-4 py-2',
    ];

    $position = $placements[$placement] ?? $placements['bottom'];
    $colorClass = $colors[$color] ?? $colors['dark'];
    $sizeClass = $sizes[$size] ?? $sizes['sm'];

    $offsetClass = match ((int) $offset) {
        0 => 'mt-0',
        4 => 'mt-1',
        8 => 'mt-2',
        12 => 'mt-3',
        16 => 'mt-4',
        default => 'mt-2',
    };
@endphp

<div x-data="{ open: false }"
    @if ($trigger === 'hover') @mouseenter="open = true"
        @mouseleave="open = false"
    @elseif($trigger === 'click')
        @click="open = !open"
        @click.outside="open = false"
    @elseif($trigger === 'focus')
        @focusin="open = true"
        @focusout="open = false" @endif
    class="relative inline-flex">
    {{-- Trigger --}}
    <div
        @if ($trigger === 'click') role="button"
            tabindex="0"
            @keydown.enter="open = !open"
            @keydown.space.prevent="open = !open" @endif>
        {{ $slot }}
    </div>

    {{-- Tooltip --}}
    <div x-cloak x-show="open" x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95" role="tooltip"
        class="absolute z-[99] {{ $position['tooltip'] }} {{ $offsetClass }} {{ $width }} whitespace-normal rounded-lg shadow-lg {{ $colorClass }} {{ $sizeClass }}"
        style="margin-top: {{ $placement === 'bottom' ? $offset : 0 }}px;
               margin-bottom: {{ $placement === 'top' ? $offset : 0 }}px;
               margin-left: {{ $placement === 'right' ? $offset : 0 }}px;
               margin-right: {{ $placement === 'left' ? $offset : 0 }}px;">
        {{ $text ?? $slot->isEmpty() ? $text : '' }}

        @if (isset($content))
            {{ $content }}
        @endif

        {{-- Arrow --}}
        <div class="absolute h-2.5 w-2.5 rotate-45 {{ $position['arrow'] }} {{ $colorClass }} border-inherit"></div>
    </div>
</div>
