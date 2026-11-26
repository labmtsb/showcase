<div class="inline-flex rounded-base shadow-xs -space-x-px" role="group">

    {{-- View --}}
    <button type="button" wire:click="viewProject({{ $project->id }})"
        class="text-body bg-neutral-primary-soft border border-default hover:bg-neutral-secondary-medium font-medium rounded-s-base text-sm px-3 py-2">
        View
    </button>

    {{-- Manage --}}
    <button type="button" wire:click="manageProject({{ $project->id }})"
        class="text-body bg-neutral-primary-soft border border-default hover:bg-neutral-secondary-medium font-medium text-sm px-3 py-2">
        Manage
    </button>

    {{-- Delete --}}
    <button type="button"
        wire:click="$dispatch('confirm-delete-project', {
            projectId: {{ $project->id }}
        })"
        class="text-red-600 bg-neutral-primary-soft border border-default hover:bg-red-50 dark:hover:bg-red-950/30 font-medium rounded-e-base text-sm px-3 py-2">
        Delete
    </button>

</div>
