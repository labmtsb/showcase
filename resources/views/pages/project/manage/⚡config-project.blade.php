<?php

use Livewire\Component;
use App\Models\Project;
use Illuminate\Validation\Rule;

new class extends Component {
    public $project = null;

    public $entry_point = '';

    public $visibility = '';

    public function mount($id)
    {
        $this->project = Project::where('user_id', Auth::id())->findOrFail($id);

        $this->entry_point = $this->project->entry_point;

        $this->visibility = $this->project->visibility;
    }

    public function update()
    {
        $this->validate([
            'entry_point' => ['required', 'regex:/^[a-zA-Z0-9_\-]+\.[a-zA-Z0-9]+$/'],
            'visibility' => ['required', Rule::in(['public', 'private', 'unlisted'])],
        ]);

        $this->project->update([
            'entry_point' => $this->entry_point,
            'visibility' => $this->visibility,
        ]);

        $this->dispatch('toast', variant: 'success', title: 'Success!', message: 'Project configuration has been updated.');
    }
};
?>

<div {{ $attributes->class(['w-full grid gap-4']) }}>
    <x-card size="full">
        <h5 class="mb-3 text-2xl font-semibold tracking-tight text-heading leading-8">
            Project configurations
        </h5>

        <p class="text-body mb-6">
            Setup your project configurations.
        </p>

        <x-form livewire action="update">

            @island(name: 'configuration', lazy: true)

                @placeholder
                    <div class="space-y-6 animate-pulse">

                        {{-- Entry Point  --}}
                        <div>
                            <div class="h-4 w-36 mb-2.5 rounded bg-neutral-secondary-medium"></div>
                            <div class="h-[46px] w-full rounded-base bg-neutral-secondary-medium"></div>
                        </div>

                        {{-- Visibility  --}}
                        <div>
                            <div class="h-4 w-32 mb-2.5 rounded bg-neutral-secondary-medium"></div>
                            <div class="h-[46px] w-full rounded-base bg-neutral-secondary-medium"></div>
                        </div>

                    </div>
                @endplaceholder

                <x-text-input name="entry_point" label="Project entry point" wire:model="entry_point">
                </x-text-input>

                <div>
                    <label for="visibility" class="block mb-2.5 text-sm font-medium text-heading">Project visibility</label>
                    <select id="visibility" wire:model="visibility"
                        class="block w-full px-3 py-2.5 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body">
                        <option value="public">Public</option>
                        <option value="private">Private</option>
                        <option value="unlisted">Unlisted</option>
                    </select>
                </div>
            @endisland
            <div class="flex justify-end mt-4">
                <x-button type="submit" label="Save" loading-label="Saving..." target="update"></x-button>
            </div>
        </x-form>


    </x-card>

    <x-card size="full">
        <h5 class="mb-3 text-2xl font-semibold tracking-tight text-heading leading-8">
            Project informations
        </h5>

        <p class="text-body mb-6">
            Your project information is displyed here.
        </p>

        @island(name: 'informaion', lazy: true)
            @placeholder
                <dl class="max-w-lg divide-y divide-default animate-pulse">

                    <!-- Name of project -->
                    <div class="flex flex-col pb-3">
                        <div class="h-3 w-28 mb-2 rounded bg-neutral-secondary-medium"></div>
                        <div class="h-5 w-48 rounded bg-neutral-secondary-medium"></div>
                    </div>

                    <!-- Slug -->
                    <div class="flex flex-col py-3">
                        <div class="h-3 w-12 mb-2 rounded bg-neutral-secondary-medium"></div>
                        <div class="h-5 w-40 rounded bg-neutral-secondary-medium"></div>
                    </div>

                    <!-- Path -->
                    <div class="flex flex-col py-3">
                        <div class="h-3 w-10 mb-2 rounded bg-neutral-secondary-medium"></div>
                        <div class="h-5 w-full rounded bg-neutral-secondary-medium"></div>
                    </div>

                    <!-- Link preview -->
                    <div class="flex flex-col py-3">
                        <div class="h-3 w-20 mb-2 rounded bg-neutral-secondary-medium"></div>
                        <div class="h-5 w-full rounded bg-neutral-secondary-medium"></div>
                    </div>

                    <!-- Share token -->
                    <div class="flex flex-col py-3">
                        <div class="h-3 w-24 mb-2 rounded bg-neutral-secondary-medium"></div>
                        <div class="h-5 w-56 rounded bg-neutral-secondary-medium"></div>
                    </div>

                    <!-- Created at -->
                    <div class="flex flex-col pt-3">
                        <div class="h-3 w-20 mb-2 rounded bg-neutral-secondary-medium"></div>
                        <div class="h-5 w-36 rounded bg-neutral-secondary-medium"></div>
                    </div>

                </dl>
            @endplaceholder

            <dl class="max-w-lg text-heading divide-y divide-default mb-6">
                <div class="flex flex-col pb-3">
                    <dt class="mb-1 text-body text-sm">Name of project</dt>
                    <dd class="text-md font-medium">{{ $project->project_name }}</dd>
                </div>

                <div class="flex flex-col py-3">
                    <dt class="mb-1 text-body text-sm">Slug</dt>
                    <dd class="text-md font-medium">{{ $project->slug }}</dd>
                </div>

                <div class="flex flex-col py-3">
                    <dt class="mb-1 text-body text-sm">Path</dt>
                    <dd class="text-md font-medium">{{ $project->path }}</dd>
                </div>

                <div class="flex flex-col py-3">
                    <dt class="mb-1 text-body text-sm">Link preview</dt>
                    <dd class="text-md font-medium">
                        <a href="{{ route('preview', ['id' => $project->id]) }}" target="_blank"
                            class="text-brand underline flex gap-1">
                            {{ route('preview', ['id' => $project->id]) }}
                            <x-lucide-square-arrow-up-right-exit class="w-3 h-3" />
                        </a>
                    </dd>
                </div>

                <div class="flex flex-col py-3">
                    <dt class="mb-1 text-body text-sm">Share token</dt>
                    <dd class="text-md font-medium">{{ $project->share_token_hash ?? '' }}</dd>
                </div>

                <div class="flex flex-col pt-3">
                    <dt class="mb-1 text-body text-sm">Created at</dt>
                    <dd class="text-md font-medium">{{ $project->created_at }}</dd>
                </div>
            </dl>
        @endisland
    </x-card>
</div>
