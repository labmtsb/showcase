<?php

use Livewire\Component;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

new class extends Component {
    public string $title = 'Projects';

    public array $breadcrumb = [['name' => 'Projects', 'url' => '#']];

    /**
     * Projects collection for the current user
     * null until loaded via wire:init for lazy loading
     */
    public $projects = null;

    /**
     * Form data project
     */
    public $projectName = '';

    // toast flag to show success message
    public $showToast = false;

    /**
     * Called by wire:init to load projects lazily
     */
    public function loadProjects()
    {
        $this->projects = Project::where('user_id', Auth::id())->get();
    }

    public function rendering($view)
    {
        $view->layoutData([
            'title' => $this->title,
            'breadcrumb' => $this->breadcrumb,
        ]);
    }

    public function save()
    {
        $this->showToast = false;

        // basic validation: only letters A-Z allowed
        $this->validate(
            [
                'projectName' => ['required', 'string', 'max:125', 'regex:/^[A-Za-z\s]+$/'],
            ],
            [
                'projectName.regex' => 'Project name may only contain letters A-Z (no numbers or symbols).',
            ],
        );

        $project = Project::create([
            'user_id' => Auth::id(),
            'project_name' => $this->projectName,
            'slug' => Str::slug($this->projectName),
            'path' => 'projects/' . Auth::id() . '/' . Str::slug($this->projectName) . '/',
        ]);

        $this->dispatch('close-modal')->to(ref: 'addNewProjectModal');

        // reload projects table
        $this->loadProjects();

        // reset form fields
        $this->projectName = '';

        // show toast success
        $this->showToast = true;
    }
};
?>

<div x-data="{ showToast: @entangle('showToast') }" x-init="$watch('showToast', value => {
    if (value) {
        setTimeout(() => {
            showToast = false;
            $wire.set('showToast', false)
        }, 3500);
    }
});">

    <!-- Toast -->
    <div x-show="showToast" x-transition x-cloak class="fixed top-5 left-1/2 -translate-x-1/2 z-50">
        <div id="toast-default"
            class="flex items-center w-full max-w-xs p-4 text-body bg-neutral-primary-soft rounded-base shadow-xs border border-default"
            role="alert">
            <div class="ms-2.5 text-sm text-gray-500 dark:text-white">Project saved successfully.</div>
            <button type="button" @click="showToast = false; $wire.set('showToast', false)"
                class="ms-auto flex items-center justify-center text-body hover:text-heading bg-transparent box-border border border-transparent hover:bg-neutral-secondary-medium focus:ring-4 focus:ring-neutral-tertiary font-medium leading-5 rounded text-sm h-8 w-8 focus:outline-none"
                aria-label="Close">
                <span class="sr-only">Close</span>
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                    fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18 17.94 6M18 18 6.06 6" />
                </svg>
            </button>
        </div>
    </div>

    <x-button label="" type="button" @click="$dispatch('open-modal', { name: 'addNewProjectModal' })" text-white
        bg-brand box-border border border-transparent hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium
        shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none cursor-pointer>

        <svg class="w-5 h-5 inline text-white dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
            width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M5 12h14m-7 7V5" />
        </svg>

        Add new project
    </x-button>

    <x-modal name="addNewProjectModal" title="Add new project">

        <x-form livewire action="save">
            <x-text-input name="projectName" label="Your porject name" placeholder="Landing Page"
                wire:model="projectName" x-ref="autofocus" required />

            <div class="flex items-center gap-3">
                <x-button type="submit" label="Add project" loading-label="Adding project..." target="save" />

                <x-button type="button" label="Cancel" variant="secondary"
                    @click="$dispatch('close-modal', { name: 'addNewProjectModal' })" />
            </div>
        </x-form>

    </x-modal>


    {{-- data table --}}
    <div class="mt-4 relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-base border border-default">
        <table class="w-full text-sm text-left rtl:text-right text-body">
            <thead class="text-sm text-body bg-neutral-secondary-soft border-b rounded-base border-default">
                <tr>
                    <th scope="col" class="px-6 py-3 font-medium">
                        Project name
                    </th>
                    <th scope="col" class="px-6 py-3 font-medium">
                        Slug
                    </th>
                    <th scope="col" class="px-6 py-3 font-medium">
                        Grade
                    </th>
                    <th scope="col" class="px-6 py-3 font-medium">
                        Likes
                    </th>
                    <th scope="col" class="px-6 py-3 font-medium">
                        Comments
                    </th>
                    <th></th>
                </tr>
            </thead>
            <tbody wire:init="loadProjects">

                {{-- Loading skeleton when projects are not yet loaded (null) --}}
                @if ($projects === null)
                    @for ($i = 0; $i < 3; $i++)
                        <tr class="bg-neutral-primary border-b border-default animate-pulse">
                            <th scope="row" class="px-6 py-4 font-medium text-heading whitespace-nowrap">
                                <div class="h-4 bg-neutral-secondary-medium rounded w-32"></div>
                            </th>
                            <td class="px-6 py-4">
                                <div class="h-4 bg-neutral-secondary-medium rounded w-28"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-4 bg-neutral-secondary-medium rounded w-10"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-4 bg-neutral-secondary-medium rounded w-10"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-4 bg-neutral-secondary-medium rounded w-10"></div>
                            </td>
                            <td>
                                <div class="inline-flex rounded-base shadow-xs -space-x-px" role="group">
                                    <div class="h-8 w-16 bg-neutral-secondary-medium rounded-s-base"></div>
                                    <div class="h-8 w-16 bg-neutral-secondary-medium rounded-e-base"></div>
                                </div>
                            </td>
                        </tr>
                    @endfor

                    {{-- Empty state after loaded --}}
                @elseif($projects->isEmpty())
                    <tr class="bg-neutral-primary border-b border-default h-50">
                        <td colspan="6">
                            <div class="flex flex-col items-center justify-center py-12">

                                <h3 class="mb-1 text-sm font-semibold text-gray-900 dark:text-white">
                                    No data found
                                </h3>
                                <p class="mb-4 text-sm text-gray-500">
                                    There are no projects to display.
                                </p>

                                <x-button label="" type="button" size="sm"
                                    @click="$dispatch('open-modal', { name: 'addNewProjectModal' })" text-white bg-brand
                                    box-border border border-transparent hover:bg-brand-strong focus:ring-4
                                    focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4
                                    py-2.5 focus:outline-none cursor-pointer>

                                    <svg class="w-5 h-5 inline text-white dark:text-white" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                        viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="M5 12h14m-7 7V5" />
                                    </svg>

                                    Add new project
                                </x-button>

                            </div>

                        </td>
                    </tr>

                    {{-- Render projects --}}
                @else
                    @foreach ($projects as $project)
                        <tr class="bg-neutral-primary border-b border-default">
                            <th scope="row" class="px-6 py-4 font-medium text-heading whitespace-nowrap">
                                {{ $project->project_name }}
                            </th>
                            <td class="px-6 py-4">
                                {{ $project->slug }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $project->grade ?? 0 }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $project->likes ?? 0 }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $project->comments ?? 0 }}
                            </td>
                            <td>

                                <div class="inline-flex rounded-base shadow-xs -space-x-px" role="group">
                                    <button type="button"
                                        class="text-body bg-neutral-primary-soft border border-default hover:bg-neutral-secondary-medium hover:text-heading focus:ring-3 focus:ring-neutral-tertiary-soft font-medium leading-5 rounded-s-base text-sm px-3 py-2 focus:outline-none">
                                        View
                                    </button>
                                    <button type="button"
                                        class="text-body bg-neutral-primary-soft border border-default hover:bg-neutral-secondary-medium hover:text-heading focus:ring-3 focus:ring-neutral-tertiary-soft font-medium leading-5 rounded-e-base text-sm px-3 py-2 focus:outline-none">
                                        Manage
                                    </button>
                                </div>

                            </td>
                        </tr>
                    @endforeach
                @endif

            </tbody>
        </table>

    </div>
</div>
