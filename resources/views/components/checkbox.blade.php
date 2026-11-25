@props(['name', 'label' => '', 'value' => 1, 'id' => null, 'hint' => null])

@php
    $id = $id ?? $name;
    $hasError = $errors->has($name);
@endphp

<div class="mb-5">

    {{-- Checkbox --}}
    <label for="{{ $id }}" class="flex items-center">

        <input type="checkbox" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}"
            aria-invalid="{{ $hasError ? 'true' : 'false' }}"
            @if ($hasError) aria-describedby="{{ $id }}-error" @endif
            {{ $attributes->class([
                'w-4 h-4 border rounded-xs bg-neutral-secondary-medium focus:ring-2 focus:ring-brand-soft',
                'border-danger-subtle text-fg-danger-strong focus:ring-danger' => $hasError,
                'border-default-medium' => !$hasError,
            ]) }} />

        @if ($label)
            <span class="ms-2 text-sm font-medium text-heading select-none">
                {{ $label }}
            </span>
        @endif

    </label>

    {{-- Helper Text --}}
    @if ($hint && !$hasError)
        <p class="mt-2 text-sm text-body">
            {{ $hint }}
        </p>
    @endif

    {{-- Validation Error --}}
    @if ($hasError)
        <p id="{{ $id }}-error" class="mt-2 text-sm text-fg-danger-strong">
            {{ $errors->first($name) }}
        </p>
    @endif

</div>
