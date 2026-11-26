<?php

use Livewire\Component;

new class extends Component {
    public $menuItems = [
        [
            'label' => 'Home',
            'route' => '/home',
            'icon' => 'house',
        ],
        [
            'label' => 'Projects',
            'route' => '/projects',
            'icon' => 'folder-root',
        ],
        [
            'label' => 'Profile',
            'route' => '/profile',
            'icon' => 'user-cog',
        ],
    ];
};
?>

<div>
    <ul class="space-y-2 font-medium">

        @foreach ($menuItems as $key => $value)
            <livewire:partial.navitem :route="$value['route']" :label="$value['label']" :icon="$value['icon']" />
        @endforeach

    </ul>
</div>
