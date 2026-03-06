<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Login extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $this->validate();
        $this->ensureIsNotRateLimited();

        $user = $this->validateCredentials();

        // Si llegamos aquÃ­, credenciales OK y can_login=true
        Auth::login($user, $this->remember);

        activity()
            ->useLog('auth')
            ->event('login_success')
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties([
                'email' => $user->email,
                'remember' => $this->remember,
            ])
            ->log('Login exitoso');

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $defaultRoute = $user->isPolicia()
            ? route('prestamos.index', absolute: false)
            : route('dashboard', absolute: false);

        $this->redirectIntended(default: $defaultRoute, navigate: true);
    }

    protected function validateCredentials(): User
    {
        // Solo usuarios con can_login = true
        $user = User::where('email', $this->email)->first();
        $canLogin = $user && $user->can_login;

        if (! $user) {
            RateLimiter::hit($this->throttleKey());

            activity()
                ->useLog('auth')
                ->event('login_failed')
                ->withProperties([
                    'email' => $this->email,
                    'reason' => 'user_not_found',
                ])
                ->log('Login fallido');

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        if (! $canLogin || ! $user->password || ! Hash::check($this->password, $user->password)) {
            RateLimiter::hit($this->throttleKey());

            activity()
                ->useLog('auth')
                ->event('login_failed')
                ->performedOn($user)
                ->withProperties([
                    'email' => $this->email,
                    'reason' => $canLogin ? 'bad_password' : 'can_login_disabled',
                ])
                ->log('Login fallido');

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        return $user;
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        $user = User::where('email', $this->email)->first();

        $logger = activity()
            ->useLog('auth')
            ->event('login_locked');

        if ($user) {
            $logger->performedOn($user);
        }

        $logger
            ->withProperties([
                'email' => $this->email,
                'seconds' => $seconds,
            ])
            ->log('Login bloqueado');

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}
