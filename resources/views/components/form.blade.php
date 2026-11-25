@props([
    'action' => null,
    'method' => 'POST',
    'livewire' => false,
    'novalidate' => false,
    'autocomplete' => 'off',
])

@php
    $method = strtoupper($method);

    $allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

    if (!in_array($method, $allowedMethods)) {
        throw new InvalidArgumentException("HTTP method [{$method}] is not supported.");
    }

    $isLivewire = $livewire || filled($action);

    $htmlMethod = in_array($method, ['GET', 'POST']) ? $method : 'POST';
@endphp

<form @if ($isLivewire && $action) wire:submit="{{ $action }}" @endif method="{{ strtolower($htmlMethod) }}"
    @if (!$isLivewire && $action) action="{{ $action }}" @endif @if (!$isLivewire && $method !== 'GET') @csrf @endif
    @if (!$isLivewire && !in_array($method, ['GET', 'POST'])) @method($method) @endif @if ($novalidate) novalidate @endif
    autocomplete="{{ $autocomplete }}" {{ $attributes->class(['']) }}>
    {{ $slot }}
</form>
