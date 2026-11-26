<?php

use Livewire\Component;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $email = '';
    public $username = '';

    public function mount()
    {
        $this->fillData();
    }

    public function fillData()
    {
        $this->email = Auth::user()->email;
        $this->username = Auth::user()->username;
    }

    public function update()
    {
        $this->validate([
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore(auth()->id())],
            'username' => ['required', 'min:5', Rule::unique('users', 'username')->ignore(auth()->id())],
        ]);

        $user = Auth::user();

        // $user->update([
        //     'username' => Hash::make($this->username),
        // ]);

        $this->reset(['email', 'username']);

        $this->fillData();

        $this->dispatch('close-modal')->to(ref: 'editProfileModal');

        $this->dispatch('toast', variant: 'success', title: 'Success!', message: 'Profile has been updated.');
    }
};
?>

<div>
    <x-button variant="primary" label="Change profile"
        @click="$dispatch('open-modal', { name: 'editProfileModal' })"></x-button>

    <x-modal name="editProfileModal" title="Change profile" description="Update your profile informations." size="md">

        <x-form livewire action="update">

            <x-text-input name="nis" label="Your NIS" value="{{ Auth::user()->nis ?? '' }}" class="cursor-not-allowed"
                disabled readonly>
                <x-slot:hint>You not allowed to change your NIS.</x-slot:hint>
            </x-text-input>

            <x-text-input name="email" label="Your new email" wire:model="email" x-ref="autofocus" required />

            <x-text-input name="username" label="Your username" wire:model="username" required />


            <div class="flex items-center gap-3">
                <x-button type="submit" label="Update" loading-label="Updating..." variant="primary" target="update" />
                <x-button type="button" label="Cancel" variant="secondary"
                    @click="$dispatch('close-modal', { name: 'editProfileModal' })" />
            </div>
        </x-form>

    </x-modal>
</div>
