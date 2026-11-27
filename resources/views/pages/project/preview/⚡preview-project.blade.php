<?php

use Livewire\Component;
use App\Models\Project;

new class extends Component {
    public string $title = 'Preview';

    public $project = null;

    public $params = [];

    public function mount($id)
    {
        $this->project = Project::where('user_id', Auth::id())->findOrFail($id);

        $this->params = [
            'user' => $this->project->user_id,
            'project' => $this->project->slug . '/index.html',
        ];
    }

    public function backToProject()
    {
        $this->redirectIntended(route('projects'), navigate: true);
    }

    public function render()
    {
        return $this->view()->layout('layouts::preview');
    }

    public function rendering($view)
    {
        $view->layoutData([
            'title' => $this->title,
        ]);
    }
};
?>

<div class="w-full">
    <div tabindex="-1"
        class="fixed z-30 flex flex-col md:flex-row justify-between w-fit w-max-[25em] p-4 bg-neutral-primary-soft border border-default rounded-base shadow-xs lg:max-w-7xl right-3 bottom-3">
        <div class="flex items-center shrink-0">
            <x-button label="" variant="secondary" wire:click="backToProject">
                <x-lucide-arrow-left class="w-5 h-5 text-white mr-1" />
                <span>Back to project</span>
            </x-button>

        </div>
    </div>

    <div class="w-full h-screen">
        <iframe src="{{ route('project.preview', $params) }}" class="w-full h-screen" title="Project Preview"
            frameborder="0"></iframe>
    </div>




</div>
