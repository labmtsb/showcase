<?php

use Livewire\Component;

new class extends Component {
    //
};
?>

<div x-on:avatar-updated.window="$wire.$island('avatar').$refresh()">

    <h5 class="mb-3 text-2xl font-semibold tracking-tight text-heading leading-8">
        Your avatar
    </h5>

    <p class="text-body mb-6">
        Here is the avatar.
    </p>


    <div class="mb-6">
        @island(name: 'avatar', lazy: true)
            @placeholder
                <div class="w-50 h-50 rounded-base bg-neutral-secondary-medium animate-pulse"></div>
            @endplaceholder
            <img class="w-50 h-50 rounded-base" src="{{ asset(Auth::user()->avatar) }}" alt="avatar">
        @endisland
    </div>

    <livewire:pages::profile.edit-avatar />
</div>
