@props(['name', 'label' => '', 'type' => 'text', 'placeholder' => '', 'id' => null, 'hint' => null])

@php
    $id = $id ?? $name;
    $hasError = $errors->has($name);
@endphp

<div class="mb-5">

    {{-- Label --}}
    @if ($label)
        <label for="{{ $id }}" @class([
            'block mb-2.5 text-sm font-medium',
            'text-fg-danger-strong' => $hasError,
            'text-heading' => !$hasError,
        ])>
            {{ $label }}
        </label>
    @endif

    {{-- Input --}}
    <input type="{{ $type }}" id="{{ $id }}" name="{{ $name }}" placeholder="{{ $placeholder }}"
        aria-invalid="{{ $hasError ? 'true' : 'false' }}"
        @if ($hasError) aria-describedby="{{ $id }}-error" @endif
        {{ $attributes->class([
            'text-sm rounded-base block w-full px-3 py-2.5 shadow-xs',
            'bg-danger-soft border border-danger-subtle text-fg-danger-strong placeholder:text-fg-danger-strong focus:ring-danger focus:border-danger' => $hasError,
            'bg-neutral-secondary-medium border border-default-medium text-heading placeholder:text-body focus:ring-brand focus:border-brand' => !$hasError,
        ]) }} />

    {{-- Helper Text --}}
    @if ($hint && !$hasError)
        <p class="mt-2 text-sm text-body">
            {{ $hint }}
        </p>
    @endif

    {{-- Validation Error --}}
    @if ($hasError)
        <p id="{{ $id }}-error" class="mt-2.5 text-sm text-fg-danger-strong">
            {{ $errors->first($name) }}
        </p>
    @endif

</div>
