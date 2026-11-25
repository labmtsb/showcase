<?php

use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public string $title = 'Login';

    // data form
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    protected function rules()
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'min:5'],
        ];
    }

    public function login()
    {
        $this->validate();

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

        $this->redirectIntended(default: route('home'), navigate: true);
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

        <livewire:form class="block w-100" :action="route('attempt_login')" state="submitLogin">
            {{-- <livewire:form class="block w-100" wire:submit="login" state="submitLogin"> --}}
            {{-- <form wire:submit="login"
            class="block w-100 bg-neutral-primary-soft block p-6 border border-default rounded-base shadow-xs"> --}}
            @if (session('response'))
                <livewire:alert :type="session('response')['type']">
                    {{ session('response')['msg'] }}
                </livewire:alert>
            @endif

            <livewire:text-input type="text" name="email" label="Your email" placeholder="id@lab.net" required />

            <livewire:text-input type="password" name="password" label="Password" required />

            <livewire:checkbox name="remember" label="Remember me" />

            <livewire:button label="Sign In" loading_label="Signing In..." target="login" state="submitLogin" />
            {{-- </form> --}}
        </livewire:form>

    </div>
</div>
