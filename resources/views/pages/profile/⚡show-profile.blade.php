<?php

use Livewire\Component;

new class extends Component {
    public string $title = 'Profile';

    public array $breadcrumb = [['name' => 'Profile', 'url' => '#']];

    public function rendering($view)
    {
        $view->layoutData([
            'title' => $this->title,
            'breadcrumb' => $this->breadcrumb,
        ]);
    }
};
?>

<div class="space-y-4">
    <x-card size="xl" padding="md">
        <h5 class="mb-3 text-2xl font-semibold tracking-tight text-heading leading-8">
            Your avatar
        </h5>

        <p class="text-body mb-6">
            Here is the avatar.
        </p>

        <img class="w-50 h-50 rounded-base" src="https://flowbite.com/docs/images/people/profile-picture-5.jpg"
            alt="Default avatar">
    </x-card>


    <x-card size="xl" padding="md">
        <h5 class="mb-3 text-2xl font-semibold tracking-tight text-heading leading-8">
            Your profile
        </h5>

        <p class="text-body mb-6">
            Here are the profile informations.
        </p>

        <dl class="max-w-lg text-heading divide-y divide-default mb-6">
            <div class="flex flex-col pb-3">
                <dt class="mb-1 text-body">NIS</dt>
                <dd class="text-lg font-medium">{{ Auth::user()->nis ?? '' }}</dd>
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

        <livewire:pages::profile.edit-profile />

    </x-card>

    <x-card size="xl" padding="md">
        <h5 class="mb-3 text-2xl font-semibold tracking-tight text-heading leading-8">
            Credential
        </h5>

        <p class="text-body mb-6">
            Update password for authentication.
        </p>

        <livewire:pages::profile.edit-password />
    </x-card>
</div>
