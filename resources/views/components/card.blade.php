@props([
    // Size
    'size' => 'md',

    // Appearance
    'variant' => 'default',
    'padding' => 'md',
    'rounded' => 'base',
    'shadow' => 'sm',

    // Custom colors
    'background' => null,
    'borderColor' => null,

    // Additional options
    'hoverable' => false,
    'divided' => false,
])

@php
    // Width
    $sizes = [
        'xs' => 'max-w-xs',
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        'full' => 'w-full max-w-none',
        'auto' => 'w-fit max-w-none',
    ];

    // Padding
    $paddings = [
        'none' => 'p-0',
        'sm' => 'p-3',
        'md' => 'p-6',
        'lg' => 'p-8',
        'xl' => 'p-10',
    ];

    // Rounded
    $roundeds = [
        'none' => 'rounded-none',
        'sm' => 'rounded-sm',
        'base' => 'rounded-base',
        'md' => 'rounded-md',
        'lg' => 'rounded-lg',
        'xl' => 'rounded-xl',
        '2xl' => 'rounded-2xl',
        'full' => 'rounded-full',
    ];

    // Shadow
    $shadows = [
        'none' => 'shadow-none',
        'xs' => 'shadow-xs',
        'sm' => 'shadow-sm',
        'md' => 'shadow-md',
        'lg' => 'shadow-lg',
        'xl' => 'shadow-xl',
    ];

    // Variant
    $variants = [
        'default' => 'bg-neutral-primary-soft border border-default',
        'outlined' => 'bg-transparent border border-default',
        'elevated' => 'bg-neutral-primary-soft border border-transparent',
        'flat' => 'bg-neutral-primary-soft border border-transparent shadow-none',
    ];

    $classes = [
        'block',
        $sizes[$size] ?? $size,
        $paddings[$padding] ?? $padding,
        $roundeds[$rounded] ?? $rounded,
        $shadows[$shadow] ?? $shadow,
        $variants[$variant] ?? $variants['default'],
        $background,
        $borderColor,
        $hoverable ? 'transition-all duration-200 hover:shadow-md' : '',
        $divided
            ? '[&>[data-card-section]+[data-card-section]]:border-t [&>[data-card-section]+[data-card-section]]:border-default'
            : '',
    ];
@endphp

<div {{ $attributes->class($classes) }}>

    {{-- Header --}}
    @if (isset($header) || isset($title) || isset($actions))
        <div class="flex items-start justify-between gap-4 mb-4">
            <div class="min-w-0 flex-1">
                @if (isset($title))
                    {{ $title }}
                @endif

                @if (isset($subtitle))
                    {{ $subtitle }}
                @endif
            </div>

            @if (isset($actions))
                <div class="shrink-0">
                    {{ $actions }}
                </div>
            @endif
        </div>

        @if (isset($header))
            {{ $header }}
        @endif
    @endif

    {{-- Main Content --}}
    @if (isset($content))
        {{ $content }}
    @else
        {{ $slot }}
    @endif

    {{-- Footer --}}
    @if (isset($footer))
        <div class="mt-6">
            {{ $footer }}
        </div>
    @endif

</div>
