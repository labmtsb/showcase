<?php

use Livewire\Component;

new class extends Component {
    public string $title = 'Login';

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
            @if (session('response'))
                <x-alert :type="session('response')['type']">
                    {{ session('response')['msg'] }}
                </x-alert>
            @endif

            <livewire:text-input type="text" name="email" label="Your email" placeholder="id@lab.net"
                value="{{ old('email') }}" required />

            <livewire:text-input type="password" name="password" label="Password" required />

            <livewire:checkbox name="remember" label="Remember me" :checked="old('remember')" />

            <livewire:button label="Sign In" loading_label="Signing In..." state="submitLogin" />
        </livewire:form>

    </div>
</div>
