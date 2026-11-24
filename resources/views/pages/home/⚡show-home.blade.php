<?php

use Livewire\Component;

new class extends Component {
    public string $title = 'Home';

    public array $breadcrumb = [];

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
    {{-- Order your soul. Reduce your wants. - Augustine --}}
</div>
