<?php

use Livewire\Component;
use App\Models\Project;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public ?int $projectToDelete = null;

    public ?string $projectName = null;

    #[On('confirm-delete-project')]
    public function confirmDelete($projectId, $projectName)
    {
        // Verify project ownership.
        Project::where('user_id', Auth::id())->findOrFail($projectId);

        $this->projectToDelete = $projectId;

        $this->projectName = $projectName;

        $this->dispatch('open-modal', name: 'deleteProjectModal');
    }

    public function deleteProject()
    {
        $project = Project::where('user_id', Auth::id())->findOrFail($this->projectToDelete);

        $path = 'projects/' . Auth::id() . '/' . $project->slug . '/';

        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->deleteDirectory($path);
        }

        $project->delete();

        $this->projectToDelete = null;

        $this->dispatch('projects-updated');

        $this->dispatch('close-modal', name: 'deleteProjectModal');

        $this->dispatch('toast', variant: 'success', title: 'Success!', message: 'Delete project successfully.');
    }
};
?>

<div>
    {{-- Delete confirmation modal --}}
    <x-modal name="deleteProjectModal" title="Delete project" description="This action cannot be undone." size="md">
        <p class="leading-relaxed text-body mb-5">
            <span class="text-dark dark:text-white block">
                Project to delete:
                <span
                    class="bg-warning-soft text-fg-warning text-sm font-medium px-1.5 py-0.5 rounded">{{ $projectName }}
                </span>
            </span>

            Are you sure you want to delete this project?
            All associated project data may also be affected.
        </p>

        <div class="flex items-center gap-3">

            <x-form livewire action="deleteProject">

                <x-button type="submit" label="Delete project" loading-label="Deleting project..." variant="danger"
                    target="deleteProject" />

            </x-form>

            <x-button type="button" label="Cancel" variant="secondary"
                @click="$dispatch('close-modal', {
                name: 'deleteProjectModal'
            })" />

        </div>
    </x-modal>
</div>
