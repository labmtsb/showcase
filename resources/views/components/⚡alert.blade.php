<?php

use Livewire\Component;

new class extends Component {
    public $type = 'info';

    public $variants = [
        'success' => [
            'title' => 'Okey!',
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

    public $variant = '';

    public function mount($variant = '')
    {
        $this->variant = $this->variants[$this->type] ?? $this->variants['info'];
    }
};
?>

<div>
    <div class="p-4 mb-4 text-sm rounded-base {{ $variant['class'] }}" role="alert">
        <span class="font-medium">
            {{ $variant['title'] }}
        </span>

        {{ $slot }}
    </div>
</div>
