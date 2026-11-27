<?php

use Livewire\Component;
use App\Models\Project;

new class extends Component {
    public string $title = 'Manage';

    public $project = '';

    public $path = '';

    public array $breadcrumb = [
        [
            'name' => 'Projects',
            'url' => '/projects',
        ],
        [
            'name' => 'Manage',
            'url' => '#',
        ],
    ];

    public function mount($id)
    {
        $this->project = Project::where('user_id', Auth::id())->findOrFail($id);

        array_push($this->breadcrumb, ['name' => $this->project->project_name, 'url' => '#']);

        $this->path = 'projects/' . $this->project->user_id . '/' . $this->project->slug;
    }

    public function rendering($view)
    {
        $view->layoutData([
            'title' => $this->title,
            'breadcrumb' => $this->breadcrumb,
        ]);
    }
};
?>

<div class="w-full grid grid-cols-5 gap-4">



    <livewire:pages::project.manage.file-tree class="col-span-3" :path="$path" />

    <x-card class="col-span-2" size="full"></x-card>

</div>
