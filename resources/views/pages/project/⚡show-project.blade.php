<?php

use Livewire\Component;

new class extends Component {
    public string $title = 'Projects';

    public array $breadcrumb = [['name' => 'Projects', 'url' => '#']];

    public function rendering($view)
    {
        $view->layoutData([
            'title' => $this->title,
            'breadcrumb' => $this->breadcrumb,
        ]);
    }
};
?>

<div>

    <livewire:pages::project.project />

</div>
