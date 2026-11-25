<?php

use Livewire\Component;

new class extends Component {
    public $name;
    public $label = '';
    public $type = 'text';
    public $placeholder = '';
};
?>

<div>
    <div class="mb-5">
        @if ($label)
            <label for="{{ $name }}"
                class="block mb-2.5 text-sm font-medium
            {{ $errors->has($name) ? 'text-fg-danger-strong' : 'text-heading' }}">
                {{ $label }}
            </label>
        @endif

        <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}"
            placeholder="{{ $placeholder }}"
            {{ $attributes->merge([
                'class' =>
                    'text-sm rounded-base block w-full px-3 py-2.5 shadow-xs ' .
                    ($errors->has($name)
                        ? 'bg-danger-soft border border-danger-subtle text-fg-danger-strong placeholder:text-fg-danger-strong focus:ring-danger focus:border-danger'
                        : 'bg-neutral-secondary-medium border border-default-medium text-heading placeholder:text-body focus:ring-brand focus:border-brand'),
            ]) }} />

        @error($name)
            <p class="mt-2.5 text-sm text-fg-danger-strong">
                {{ $message }}
            </p>
        @enderror
    </div>
</div>
