<?php

use Livewire\Component;

new class extends Component {
    public $action = null;
    public $method = 'POST';
    public $hasFiles = false;
    public $state = null;
};
?>

{{-- <div>
    <form action="{{ $action }}" method="{{ strtoupper($method) === 'GET' ? 'GET' : 'POST' }}"
        @if ($hasFiles) enctype="multipart/form-data" @endif
        @if ($state) x-data="{ {{ $state }}: false }"
        @submit="{{ $state }} = true" @endif
        {{ $attributes->merge([
            'class' => 'bg-neutral-primary-soft block p-6 border border-default rounded-base shadow-xs',
        ]) }}>

        @unless (in_array(strtoupper($method), ['GET', 'POST']))
            @method($method)
        @endunless

        @unless (strtoupper($method) === 'GET')
            @csrf
        @endunless

        {{ $slot }}
    </form>
</div> --}}

<div>
    <form
        @if ($action) action="{{ $action }}"
            method="{{ strtoupper($method) === 'GET' ? 'GET' : 'POST' }}" @endif
        @if ($hasFiles) enctype="multipart/form-data" @endif
        @if ($state) x-data="{ {{ $state }}: false }"
            @submit="{{ $state }} = true" @endif
        {{ $attributes->merge([
            'class' => 'bg-neutral-primary-soft block p-6 border border-default rounded-base shadow-xs',
        ]) }}>
        @if ($action)
            @unless (in_array(strtoupper($method), ['GET', 'POST']))
                @method($method)
            @endunless

            @unless (strtoupper($method) === 'GET')
                @csrf
            @endunless
        @endif

        {{ $slot }}
    </form>
</div>
