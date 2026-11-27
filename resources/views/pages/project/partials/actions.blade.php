<div class="inline-flex rounded-base shadow-xs -space-x-px" role="group">

    {{-- View --}}
    <a href="{{ route('preview', ['id' => $project->id]) }}" type="button" wire:navigate.hover
        class="cursor-pointer text-body bg-neutral-primary-soft border border-default hover:bg-neutral-secondary-medium font-medium rounded-s-base text-sm px-3 py-2">
        Preview
    </a>

    {{-- Manage --}}
    <a href="{{ route('manage', ['id' => $project->id]) }}" type="button" wire:navigate.hover
        class="cursor-pointer text-body bg-neutral-primary-soft border border-default hover:bg-neutral-secondary-medium font-medium text-sm px-3 py-2">
        Manage
    </a>

    {{-- Delete --}}
    <button type="button"
        wire:click="$dispatch('confirm-delete-project', {
            projectId: {{ $project->id }},
            projectName: '{{ $project->project_name }}'
        })"
        class="cursor-pointer text-red-600 bg-neutral-primary-soft border border-default hover:bg-red-50 dark:hover:bg-red-950/30 font-medium rounded-e-base text-sm px-3 py-2">
        Delete
    </button>

</div>
