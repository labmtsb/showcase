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
        <livewire:pages::profile.avatar />
    </x-card>

    <x-card size="xl" padding="md">
        <livewire:pages::profile.profile />
    </x-card>

    <x-card size="xl" padding="md">
        <livewire:pages::profile.password />
    </x-card>

</div>
