<?php

use Livewire\Component;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $projects = null;

    /**
     * Called by wire:init to load projects lazily
     */
    public function loadProjects()
    {
        $this->projects = Project::where('user_id', Auth::id())->get();
    }
};
?>

{{-- <div x-on:projects-updated.window="$wire.$island('projects').$refresh()"> --}}
<div wire:init="loadProjects" x-on:projects-updated.window="$wire.$island('projects').loadProjects()">

    {{-- Toolbar --}}
    <div class="flex justify-between items-center mb-4">

        <h2 class="text-xl font-semibold text-heading">
            Projects
        </h2>

        <livewire:pages::project.create-project />

    </div>

    <x-data-table :data="$projects" :columns="[
        [
            'label' => 'Project name',
            'field' => 'project_name',
            'skeletonWidth' => '130px',
        ],
        [
            'label' => 'Slug',
            'field' => 'slug',
            'skeletonWidth' => '100px',
        ],
        [
            'label' => 'Grade',
            'field' => 'grade',
        ],
        [
            'label' => 'Likes',
            'field' => 'likes',
        ],
        [
            'label' => 'Comments',
            'field' => 'comments',
        ],
    ]" :loading="$projects === null" :row-actions="fn($project) => view('pages.project.partials.actions', ['project' => $project])" actions-label="Actions"
        skeleton="3" empty-title="No projects found" empty-description="Start by creating your first project." hoverable>

        {{-- Empty action --}}
        <x-slot:emptyAction>
            <x-button type="button" label=""
                @click="$dispatch('open-modal', {
                name: 'addNewProjectModal'
            })">
                <x-lucide-plus class="w-5 h-5 mr-1" />

                Add new project
            </x-button>
        </x-slot:emptyAction>

    </x-data-table>

    <livewire:pages::project.delete-project />
</div>
