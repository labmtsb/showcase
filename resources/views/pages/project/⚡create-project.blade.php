<?php

use Livewire\Component;
use App\Models\Project;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    public $projectName = '';

    public function save()
    {
        // basic validation: only letters A-Z allowed
        $this->validate(
            [
                'projectName' => ['required', 'string', 'max:125', 'regex:/^[A-Za-z\s]+$/', Rule::unique('projects', 'project_name')->where(fn($query) => $query->where('user_id', Auth::id()))],
            ],
            [
                'projectName.regex' => 'Project name may only contain letters A-Z (no numbers or symbols).',
                'projectName.unique' => 'You already have a project with this name.',
            ],
        );

        $path = 'projects/' . Auth::id() . '/' . Str::slug($this->projectName) . '/';

        $project = Project::create([
            'user_id' => Auth::id(),
            'project_name' => $this->projectName,
            'slug' => Str::slug($this->projectName),
            'path' => $path,
        ]);

        // buat folder project di storage
        if (!Storage::disk('local')->exists($path)) {
            Storage::disk('local')->makeDirectory($path);
        }

        $this->reset(['projectName']);

        $this->dispatch('projects-updated');

        $this->dispatch('close-modal')->to(ref: 'addNewProjectModal');

        $this->dispatch('toast', variant: 'success', title: 'Success!', message: 'Add new project successfully.');
    }
};
?>

<div>
    <x-button type="button" label=""
        @click="$dispatch('open-modal', {
                name: 'addNewProjectModal'
            })">
        <x-lucide-plus class="w-5 h-5 mr-1" />

        Add new project
    </x-button>

    <x-modal name="addNewProjectModal" title="Add new project">

        <x-form livewire action="save">
            <x-text-input name="projectName" label="Your porject name" placeholder="Landing Page" wire:model="projectName"
                x-ref="autofocus" required />

            <div class="flex items-center gap-3">
                <x-button type="submit" label="Add project" loading-label="Adding project..." target="save" />

                <x-button type="button" label="Cancel" variant="secondary"
                    @click="$dispatch('close-modal', { name: 'addNewProjectModal' })" />
            </div>
        </x-form>

    </x-modal>
</div>
