@props([
    // Position
    'placement' => 'top-right',

    // Default appearance
    'variant' => 'success',
    'theme' => 'auto',

    // Behavior
    'duration' => 4000,
    'dismissible' => true,
    'progress' => true,

    // Layout
    'max' => 5,
    'width' => 'sm',

    // Additional
    'showIcon' => true,
])

@php
    $placements = [
        'top' => 'top-5 left-1/2 -translate-x-1/2',
        'top-left' => 'top-5 left-5',
        'top-right' => 'top-5 right-5',
        'bottom' => 'bottom-5 left-1/2 -translate-x-1/2',
        'bottom-left' => 'bottom-5 left-5',
        'bottom-right' => 'bottom-5 right-5',
        'center' => 'top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2',
    ];

    $widths = [
        'xs' => 'w-72',
        'sm' => 'w-80',
        'md' => 'w-96',
        'lg' => 'w-[28rem]',
        'full' => 'w-full max-w-md',
    ];

    $placementClass = $placements[$placement] ?? $placements['top-right'];
    $widthClass = $widths[$width] ?? $widths['sm'];

    $themeClass = match ($theme) {
        'light' => 'bg-white text-gray-900 border-gray-200',
        'dark' => 'bg-gray-900 text-white border-gray-700',
        default => 'bg-white text-gray-900 border-gray-200 dark:bg-gray-900 dark:text-white dark:border-gray-700',
    };
@endphp

<div wire:ignore x-data="{
    toasts: [],
    nextId: 1,

    defaults: {
        variant: @js($variant),
        duration: @js((int) $duration),
        dismissible: @js((bool) $dismissible),
        progress: @js((bool) $progress),
        showIcon: @js((bool) $showIcon),
    },

    max: @js((int) $max),

    addToast(detail = {}) {
        const toast = {
            id: this.nextId++,
            title: detail.title ?? '',
            message: detail.message ?? detail.text ?? '',
            variant: detail.variant ?? detail.type ?? this.defaults.variant,
            duration: Number(detail.duration ?? this.defaults.duration),
            dismissible: detail.dismissible ?? this.defaults.dismissible,
            progress: detail.progress ?? this.defaults.progress,
            showIcon: detail.showIcon ?? this.defaults.showIcon,
            icon: detail.icon ?? null,
            closing: false,
        };

        // Remove oldest toast when limit is reached
        while (this.toasts.length >= this.max) {
            this.toasts.shift();
        }

        this.toasts.push(toast);

        // Auto dismiss
        if (toast.duration > 0) {
            setTimeout(() => {
                this.removeToast(toast.id);
            }, toast.duration);
        }
    },

    removeToast(id) {
        const toast = this.toasts.find(item => item.id === id);

        if (!toast || toast.closing) return;

        toast.closing = true;

        setTimeout(() => {
            this.toasts = this.toasts.filter(item => item.id !== id);
        }, 200);
    },

    getIcon(variant) {
        const icons = {
            success: '✓',
            danger: '✕',
            error: '✕',
            warning: '!',
            info: 'i',
            neutral: '•',
        };

        return icons[variant] ?? '•';
    },

    getVariantClass(variant) {
        const classes = {
            success: 'border-green-200 bg-green-50 text-green-900 dark:border-green-900 dark:bg-green-950 dark:text-green-100',
            danger: 'border-red-200 bg-red-50 text-red-900 dark:border-red-900 dark:bg-red-950 dark:text-red-100',
            error: 'border-red-200 bg-red-50 text-red-900 dark:border-red-900 dark:bg-red-950 dark:text-red-100',
            warning: 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-100',
            info: 'border-blue-200 bg-blue-50 text-blue-900 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-100',
            neutral: 'border-gray-200 bg-white text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
        };

        return classes[variant] ?? classes.neutral;
    },

    getIconClass(variant) {
        const classes = {
            success: 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
            danger: 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
            error: 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
            warning: 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300',
            info: 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
            neutral: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        };

        return classes[variant] ?? classes.neutral;
    },

    getProgressClass(variant) {
        const classes = {
            success: 'bg-green-500',
            danger: 'bg-red-500',
            error: 'bg-red-500',
            warning: 'bg-amber-500',
            info: 'bg-blue-500',
            neutral: 'bg-gray-500',
        };

        return classes[variant] ?? classes.neutral;
    },
}" x-on:toast.window="addToast($event.detail)"
    x-on:notify.window="addToast($event.detail)"
    class="fixed {{ $placementClass }} z-[9999] flex flex-col gap-3 pointer-events-none {{ $widthClass }}"
    aria-live="polite" aria-atomic="false">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="!toast.closing" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-2 scale-95" :class="getVariantClass(toast.variant)"
            class="relative overflow-hidden flex items-start gap-3 p-4 rounded-xl border shadow-lg pointer-events-auto"
            :role="['danger', 'error'].includes(toast.variant) ? 'alert' : 'status'">

            {{-- Icon --}}
            <template x-if="toast.showIcon">
                <div class="flex items-center justify-center shrink-0 w-8 h-8 rounded-full font-bold text-sm"
                    :class="getIconClass(toast.variant)">
                    <span x-text="toast.icon ?? getIcon(toast.variant)"></span>
                </div>
            </template>

            {{-- Content --}}
            <div class="min-w-0 flex-1 pt-0.5">
                <template x-if="toast.title">
                    <h4 class="font-semibold text-sm mb-1" x-text="toast.title"></h4>
                </template>

                <p class="text-sm leading-relaxed opacity-90 break-words" x-text="toast.message"></p>
            </div>

            {{-- Close Button --}}
            <template x-if="toast.dismissible">
                <button type="button" @click="removeToast(toast.id)"
                    class="shrink-0 p-1 rounded-lg opacity-60 hover:opacity-100 hover:bg-black/5 dark:hover:bg-white/10 transition"
                    aria-label="Close notification">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round">
                        <path d="m18 6-12 12M6 6l12 12" />
                    </svg>
                </button>
            </template>

            {{-- Progress Bar --}}
            <template x-if="toast.progress && toast.duration > 0">
                <div class="absolute bottom-0 left-0 w-full h-1 bg-black/5 dark:bg-white/10">
                    <div class="h-full transition-[width] ease-linear" :class="getProgressClass(toast.variant)"
                        :style="{
                            width: '100%',
                            animation: `toast-progress ${toast.duration}ms linear forwards`
                        }">
                    </div>
                </div>
            </template>

        </div>
    </template>
</div>

<style>
    @keyframes toast-progress {
        from {
            width: 100%;
        }

        to {
            width: 0%;
        }
    }
</style>
