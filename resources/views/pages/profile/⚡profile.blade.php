<?php

use Livewire\Component;

new class extends Component {
    //
};
?>

<div x-on:profile-updated.window="$wire.$island('profile').$refresh()">

    <h5 class="mb-3 text-2xl font-semibold tracking-tight text-heading leading-8">
        Your profile
    </h5>

    <p class="text-body mb-6">
        Here are the profile informations.
    </p>

    @island(name: 'profile', lazy: true)
        @placeholder
            <dl class="max-w-lg divide-y divide-default mb-6 animate-pulse">
                <div class="flex flex-col pb-3">
                    <div class="h-4 w-16 bg-neutral-secondary-medium rounded mb-2"></div>
                    <div class="h-6 w-32 bg-neutral-secondary-medium rounded"></div>
                </div>

                <div class="flex flex-col py-3">
                    <div class="h-4 w-16 bg-neutral-secondary-medium rounded mb-2"></div>
                    <div class="h-6 w-32 bg-neutral-secondary-medium rounded"></div>
                </div>

                <div class="flex flex-col py-3">
                    <div class="h-4 w-24 bg-neutral-secondary-medium rounded mb-2"></div>
                    <div class="h-6 w-64 bg-neutral-secondary-medium rounded"></div>
                </div>

                <div class="flex flex-col pt-3">
                    <div class="h-4 w-20 bg-neutral-secondary-medium rounded mb-2"></div>
                    <div class="h-6 w-40 bg-neutral-secondary-medium rounded"></div>
                </div>
            </dl>
        @endplaceholder

        <dl class="max-w-lg text-heading divide-y divide-default mb-6">
            <div class="flex flex-col pb-3">
                <dt class="mb-1 text-body">NIS</dt>
                <dd class="text-lg font-medium">{{ Auth::user()->nis ?? '' }}</dd>
            </div>

            <div class="flex flex-col pb-3">
                <dt class="mb-1 text-body">Name</dt>
                <dd class="text-lg font-medium">{{ Auth::user()->name }}</dd>
            </div>

            <div class="flex flex-col py-3">
                <dt class="mb-1 text-body">Email address</dt>
                <dd class="text-lg font-medium">{{ Auth::user()->email }}</dd>
            </div>

            <div class="flex flex-col pt-3">
                <dt class="mb-1 text-body">Username</dt>
                <dd class="text-lg font-medium">{{ Auth::user()->username }}</dd>
            </div>
        </dl>
    @endisland

    <livewire:pages::profile.edit-profile />

</div>
