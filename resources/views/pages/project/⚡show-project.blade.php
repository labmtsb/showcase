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

    public $slug = null;

    // signal to frontend to close modal after save (entangled with Alpine)
    public $closeModal = false;

    // toast flag to show success message
    public $showToast = false;

    // server-side saving flag
    public $isSaving = false;

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
        // set saving flag so frontend can react immediately
        $this->isSaving = true;
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

        // reload projects table
        $this->loadProjects();

        // reset form fields
        $this->projectName = '';
        $this->slug = null;

        // notify frontend to close modal by setting entangled flag
        $this->closeModal = true;

        // show toast success
        $this->showToast = true;

        // reset server saving flag
        $this->isSaving = false;
    }
};
?>

<div x-data="{ openAddNewProjectModal: false, projectName: @entangle('projectName').defer, slug: @entangle('slug').defer, closeModal: @entangle('closeModal'), showToast: @entangle('showToast'), isSaving: false }" x-init="$watch('closeModal', value => {
    if (value) {
        openAddNewProjectModal = false;
        // reset local saving flag when server signals close
        isSaving = false;
        setTimeout(() => { $wire.set('closeModal', false) }, 250);
    }
});

// auto-hide toast when shown
$watch('showToast', value => {
    if (value) {
        setTimeout(() => {
            showToast = false;
            $wire.set('showToast', false)
        }, 3500);
    }
});

$watch('openAddNewProjectModal', value => {
    if (value) {
        $nextTick(() => { if ($refs.projectNameInput) $refs.projectNameInput.focus() })
    }
});">
    <!-- New Project Modal toggle -->

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
    <button @click="openAddNewProjectModal = true"
        class="text-white bg-brand box-border border border-transparent hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none cursor-pointer"
        type="button">

        <svg class="w-5 h-5 inline text-white dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
            width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M5 12h14m-7 7V5" />
        </svg>

        Add new project
    </button>

    <!-- New Project Modal -->
    <div x-show="openAddNewProjectModal" x-transition x-cloak
        class="overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 flex justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-black/30">
        <div class="relative p-4 w-full max-w-2xl max-h-full" @click.outside="openAddNewProjectModal = false">
            <!-- Modal content -->
            <form @submit.prevent="isSaving = true; $wire.call('save')"
                class="relative bg-neutral-primary-soft border border-default rounded-base shadow-sm p-4 md:p-6">
                <!-- Modal header -->
                <div class="flex items-center justify-between border-b border-default pb-4 md:pb-5">
                    <h3 class="text-lg font-medium text-heading">
                        Add new project
                    </h3>
                    <button type="button" @click="openAddNewProjectModal = false"
                        class="text-body bg-transparent hover:bg-neutral-tertiary hover:text-heading rounded-base text-sm w-9 h-9 ms-auto inline-flex justify-center items-center">
                        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                            height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18 17.94 6M18 18 6.06 6" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="space-y-4 md:space-y-6 py-4 md:py-6">
                    <div class="mb-4">
                        <label for="project-name" class="block mb-2.5 text-sm font-medium text-heading">Your project
                            name</label>
                        <input type="text" id="project-name" x-ref="projectNameInput" wire:model="projectName"
                            class="text-sm rounded-base block w-full px-3 py-2.5 shadow-xs {{ $errors->has('projectName') ? 'bg-danger-soft border border-danger-subtle text-fg-danger-strong placeholder:text-fg-danger-strong focus:ring-danger focus:border-danger' : 'bg-neutral-secondary-medium border border-default-medium text-heading placeholder:text-body focus:ring-brand focus:border-brand' }}"
                            placeholder="Landing Page" required />
                        @error('projectName')
                            <p class="mt-2.5 text-sm text-fg-danger-strong">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="flex items-center border-t border-default space-x-4 pt-4 md:pt-5">
                    <button type="submit" wire:loading.attr="disabled" wire:target="save"
                        class="text-white box-border border border-transparent focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none"
                        :class="isSaving ? 'bg-gray-400 cursor-not-allowed' : 'bg-brand hover:bg-brand-strong'">
                        <span wire:loading.remove wire:target="save">Add</span>
                        <span wire:loading wire:target="save" class="inline-flex items-center space-x-2">
                            <svg class="w-4 h-4 mr-2 animate-spin inline" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>Saving...</span>
                        </span>
                    </button>
                    <button @click="openAddNewProjectModal = false" type="button"
                        class="text-body bg-neutral-secondary-medium box-border border border-default-medium hover:bg-neutral-tertiary-medium hover:text-heading focus:ring-4 focus:ring-neutral-tertiary shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none">Cancel</button>
                </div>
            </form>
        </div>
    </div>

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
                                <button type="button" @click="openAddNewProjectModal = true"
                                    class="text-white bg-brand hover:bg-brand-strong box-border border border-transparent focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-xs px-3 py-1.5 focus:outline-none cursor-pointer">

                                    <svg class="w-5 h-5 inline text-white dark:text-white" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="M5 12h14m-7 7V5" />
                                    </svg>

                                    Add new project

                                </button>

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
