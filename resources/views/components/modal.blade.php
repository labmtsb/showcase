@props([
    'name',
    'title' => null,
    'description' => null,
    'size' => 'md',
    'closeButton' => true,
    'closeOnOutside' => true,
    'closeOnEscape' => true,
])

@php
    $sizes = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<div wire:ref="{{ $name }}" x-data="{ open: false }"
    x-on:open-modal.window="if ($event.detail?.name === @js($name)) { open = true; $nextTick(() => { if ($refs.autofocus) { try { $refs.autofocus.focus(); } catch(e){} } }) }"
    x-on:close-modal.window="if ($event.detail?.name === @js($name)) open = false"
    x-on:close-modal="open = false">
    @teleport('body')
        <div>
            <div x-show="open" x-cloak x-transition.opacity.duration.200ms
                @if ($closeOnEscape) x-on:keydown.escape.window="open = false" @endif
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/30 p-4"
                @if ($closeOnOutside) x-on:click.self="open = false" @endif role="dialog" aria-modal="true"
                @if ($title) aria-label="{{ $title }}" @endif>
                {{-- Modal Container --}}
                <div x-show="open" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="relative w-full {{ $sizeClass }} max-h-[calc(100vh-2rem)] overflow-y-auto bg-neutral-primary-soft border border-default rounded-base shadow-sm p-4 md:p-6">
                    {{-- Modal Header --}}
                    @if ($title || $closeButton)
                        <div class="flex items-center justify-between border-b border-default pb-4 md:pb-5">

                            <div>
                                @if ($title)
                                    <h3 class="text-lg font-medium text-heading">
                                        {{ $title }}
                                    </h3>
                                @endif

                                @if ($description)
                                    <p class="mt-1 text-sm text-body">
                                        {{ $description }}
                                    </p>
                                @endif
                            </div>

                            @if ($closeButton)
                                <button type="button" x-on:click="open = false"
                                    class="text-body bg-transparent hover:bg-neutral-tertiary hover:text-heading rounded-base text-sm w-9 h-9 ms-auto inline-flex justify-center items-center shrink-0"
                                    aria-label="Close modal">
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6" />
                                    </svg>
                                </button>
                            @endif

                        </div>
                    @endif

                    {{-- Modal Body --}}
                    <div class="py-4 md:py-6">
                        {{ $slot }}
                    </div>

                    {{-- Modal Footer --}}
                    @isset($footer)
                        <div class="flex items-center border-t border-default pt-4 md:pt-5">
                            {{ $footer }}
                        </div>
                    @endisset

                </div>
            </div>
        </div>

    @endteleport
</div>
