<?php

use Livewire\Component;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $avatar = '';

    public function update()
    {
        $user = Auth::user();

        $user->update([
            'avatar' => '/storage/avatars/' . $this->avatar . '.svg',
        ]);

        $this->dispatch('avatar-updated');

        $this->dispatch('close-modal')->to(ref: 'editAvatarModal');

        $this->dispatch('toast', variant: 'success', title: 'Success!', message: 'Avatar has been updated.');
    }
};
?>

<div>
    <x-button variant="primary" label="Change profile"
        @click="$dispatch('open-modal', { name: 'editAvatarModal' })"></x-button>

    <x-modal name="editAvatarModal" title="Change avatar" description="Choose your avatar picture." size="md">

        <x-form livewire action="update">

            <div class="grid grid-cols-4 sm:grid-cols-5 gap-4 mb-6">
                @for ($i = 1; $i <= 20; $i++)
                    <label class="relative cursor-pointer">
                        <input type="radio" name="avatar" wire:model.live="avatar" value="{{ $i }}"
                            class="peer sr-only">

                        <div
                            class="transition-all
                       peer-checked:border-primary-500
                       peer-checked:bg-primary-50">
                            <img src="{{ asset("storage/avatars/{$i}.svg") }}" alt="Avatar {{ $i }}"
                                class="w-16 h-16 ">
                        </div>

                        <div
                            class="absolute -top-2 -right-2 hidden peer-checked:flex
                       w-6 h-6 rounded-full bg-success dark:text-white text-gray-900
                       items-center justify-center">
                            <x-lucide-circle-check class="w-4 h-4" />
                        </div>
                    </label>
                @endfor
            </div>

            <div class="flex items-center gap-3">
                <x-button type="submit" label="Update" loading-label="Updating..." variant="primary" target="update" />
                <x-button type="button" label="Cancel" variant="secondary"
                    @click="$dispatch('close-modal', { name: 'editAvatarModal' })" />
            </div>
        </x-form>

    </x-modal>
</div>
