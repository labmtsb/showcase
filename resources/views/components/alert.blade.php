@props([
    'type' => 'info',
    'title' => null,
    'dismissible' => false,
    'show' => true,
])

@php
    $variants = [
        'success' => [
            'title' => 'Success!',
            'class' => 'text-fg-success-strong bg-success-soft',
        ],
        'error' => [
            'title' => 'Oops!',
            'class' => 'text-fg-danger-strong bg-danger-soft',
        ],
        'warning' => [
            'title' => 'Warning!',
            'class' => 'text-fg-warning-strong bg-warning-soft',
        ],
        'info' => [
            'title' => 'Info!',
            'class' => 'text-fg-info-strong bg-info-soft',
        ],
    ];

    $variant = $variants[$type] ?? $variants['info'];

    $alertTitle = $title ?? $variant['title'];

    $alertId = 'alert-' . \Illuminate\Support\Str::uuid();
@endphp

@if ($show)
    <div id="{{ $alertId }}" role="alert"
        {{ $attributes->class(['p-4 mb-4 text-sm rounded-base flex gap-3 items-start', $variant['class']]) }}
        @if ($dismissible) x-data="{ open: true }"
            x-show="open"
            x-transition.opacity.duration.200ms @endif>

        {{-- Content --}}
        <div class="flex-1">
            @if ($alertTitle)
                <span class="font-medium">
                    {{ $alertTitle }}
                </span>
            @endif

            <div>
                {{ $slot }}
            </div>
        </div>

        {{-- Close Button --}}
        @if ($dismissible)
            <button type="button" @click="open = false"
                class="inline-flex items-center justify-center w-6 h-6 rounded-base hover:bg-black/10 focus:outline-none shrink-0"
                aria-label="Close alert">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        @endif
    </div>
@endif
