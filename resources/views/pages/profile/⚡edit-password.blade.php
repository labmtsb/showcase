<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $current_password = '';
    public $password = '';
    public $password_confirmation = '';

    public function update()
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'min:5', 'confirmed'],
        ]);

        $user = Auth::user();

        $user->update([
            'password' => Hash::make($this->password),
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);

        $this->dispatch('close-modal')->to(ref: 'editPasswordModal');

        $this->dispatch('toast', variant: 'success', title: 'Success!', message: 'Password has been updated.');
    }
};
?>

<div>
    <x-button variant="primary" label="Change password"
        @click="$dispatch('open-modal', { name: 'editPasswordModal' })"></x-button>

    <x-modal name="editPasswordModal" title="Change password" description="You will need to fill the current password."
        size="md">

        <x-form livewire action="update">

            <x-text-input type="password" name="current_password" label="Your current password"
                wire:model="current_password" x-ref="autofocus" required />

            <x-text-input type="password" name="password" label="Your new password" wire:model="password" required />

            <x-text-input type="password" name="password_confirmation" label="Password confirmation"
                wire:model="password_confirmation" required />


            <div class="flex items-center gap-3">
                <x-button type="submit" label="Update" loading-label="Updating..." variant="primary" target="update" />
                <x-button type="button" label="Cancel" variant="secondary"
                    @click="$dispatch('close-modal', { name: 'editPasswordModal' })" />
            </div>
        </x-form>

    </x-modal>
</div>
