<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
       public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            // Suma el intento fallido tanto a la IP como al correo por separado (bloqueo de 300 seg = 5 min)
            RateLimiter::hit($this->ipThrottleKey(), 300);
            RateLimiter::hit($this->emailThrottleKey(), 300);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->ipThrottleKey());
        RateLimiter::clear($this->emailThrottleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        // Bloquea si se superaron los 5 intentos en la misma IP O en el mismo correo
        if (! RateLimiter::tooManyAttempts($this->ipThrottleKey(), 5) &&
            ! RateLimiter::tooManyAttempts($this->emailThrottleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = max(
            RateLimiter::availableIn($this->ipThrottleKey()),
            RateLimiter::availableIn($this->emailThrottleKey())
        );

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    // Candado 1: Bloquea la IP aunque el script cambie el correo en cada intento
    public function ipThrottleKey(): string
    {
        return 'login_ip|' . $this->ip();
    }

    // Candado 2: Bloquea el correo (sin espacios) aunque el script cambie de IP
    public function emailThrottleKey(): string
    {
        return 'login_email|' . Str::transliterate(Str::lower(trim($this->string('email'))));
    }
}
