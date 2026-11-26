<?php

use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public string $title = 'Login';

    // data form
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function login()
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:5'],
        ]);

        $attempt = Auth::attempt(
            [
                'email' => Str::of($this->email)->trim(),
                'password' => $this->password,
            ],
            $this->remember,
        );

        if (!$attempt) {
            session()->flash('response', [
                'type' => 'error',
                'msg' => 'Invalid email or password.',
            ]);

            return;
        }

        session()->regenerate();

        $this->redirectIntended(route('home'), navigate: true);
    }

    public function render()
    {
        return $this->view()->layout('layouts::guest');
    }

    public function rendering($view)
    {
        $view->layoutData([
            'title' => $this->title,
        ]);
    }
};
?>

<div>
    <div class="w-full h-screen flex flex-col items-center justify-center gap-4">

        <x-form livewire action="login"
            class="block w-100 bg-neutral-primary-soft block p-6 border border-default rounded-base shadow-xs">

            <h5 class="text-xl font-semibold text-heading mb-6">Sign in to showcase panel</h5>

            @if (session('response'))
                <x-alert :type="session('response')['type']" dismissible>
                    {{ session('response')['msg'] }}
                </x-alert>
            @endif

            <x-text-input name="email" label="Your email" placeholder="id@lab.net" wire:model="email" required />

            <x-text-input name="password" label="Your password" type="password" wire:model="password" required />

            <x-checkbox name="remember" label="Remember me" wire:model="remember" />

            <x-button type="submit" label="Sign in" loading-label="Signing in..." target="login" full />
        </x-form>

    </div>
</div>
