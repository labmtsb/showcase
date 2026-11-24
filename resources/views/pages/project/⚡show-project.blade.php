<?php

use Livewire\Component;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public string $title = 'Projects';

    public array $breadcrumb = [['name' => 'Projects', 'url' => '#']];

    /**
     * Projects collection for the current user
     * null until loaded via wire:init for lazy loading
     */
    public $projects = null;

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
};
?>

<div x-data="{ openAddNewProjectModal: false }">
    <!-- New Project Modal toggle -->
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
            <form class="relative bg-neutral-primary-soft border border-default rounded-base shadow-sm p-4 md:p-6">
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
                    <div class="">
                        <label for="project-name" class="block mb-2.5 text-sm font-medium text-heading">Your project
                            name</label>
                        <input type="text" id="project-name"
                            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body"
                            placeholder="Landing Page" required />
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="flex items-center border-t border-default space-x-4 pt-4 md:pt-5">
                    <button type="submit"
                        class="text-white bg-brand box-border border border-transparent hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none">Add</button>
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
                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                        viewBox="0 0 24 24">
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
