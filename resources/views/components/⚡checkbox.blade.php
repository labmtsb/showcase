<?php

use Livewire\Component;

new class extends Component {
    public $name;
    public $label = '';
    public $value = 1;
};
?>

<div>

    <label for="{{ $name }}" class="flex items-center mb-5">
        <input id="{{ $name }}" name="{{ $name }}" type="checkbox" value="{{ $value }}"
            {{ $attributes->merge([
                'class' =>
                    'w-4 h-4 border border-default-medium rounded-xs bg-neutral-secondary-medium focus:ring-2 focus:ring-brand-soft',
            ]) }} />

        <span class="ms-2 text-sm font-medium text-heading select-none">
            {{ $label }}
        </span>
    </label>

</div>
