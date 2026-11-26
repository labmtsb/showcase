<?php

use Livewire\Component;

new class extends Component {
    public function logout()
    {
        Auth::logout();

        session()->regenerate();

        $this->redirectIntended(route('login'), navigate: true);
    }
};
?>

<div class="flex items-center ms-3" x-data="{ openUserMenu: false }">
    <div class="relative">
        <button type="button"
            class="flex text-sm bg-gray-800 rounded-full focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600"
            @click="openUserMenu = true">
            <span class="sr-only">Open user menu</span>
            <img class="w-8 h-8 rounded-full" src="https://flowbite.com/docs/images/people/profile-picture-5.jpg"
                alt="user photo">
        </button>


        <div x-show="openUserMenu" @click.outside="openUserMenu = false" x-transition x-cloak
            class="z-50 bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-44 absolute top-12 right-0 mt-2">
            <div class="px-4 py-3 border-b border-default-medium" role="none">
                <p class="text-sm font-medium text-heading" role="none">
                    {{ Auth::user()->name }}
                </p>
                <p class="text-sm text-body truncate" role="none">
                    {{ Auth::user()->email }}
                </p>
            </div>
            <ul class="p-2 text-sm text-body font-medium" role="none">
                <li>
                    <button type="button" @click="$dispatch('open-modal', { name: 'logoutModal' })"
                        class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">
                        Sign out
                    </button>
                    <x-modal name="logoutModal" title="Sign out from your account"
                        description="You will need to sign in again to access your account." size="md">
                        <p class="leading-relaxed text-body mb-3">

                            Are you sure you want to sign out?
                        </p>

                        <div class="flex items-center gap-3">

                            <x-form livewire action="logout">
                                <x-button type="submit" label="Sign Out" loading-label="Signing out..."
                                    variant="danger" target="logout" />
                            </x-form>

                            <x-button type="button" label="Cancel" variant="secondary"
                                @click="$dispatch('close-modal', { name: 'logoutModal' })" />

                        </div>

                    </x-modal>
                </li>


            </ul>
        </div>
    </div>
</div>
