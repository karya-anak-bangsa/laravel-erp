<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    private const MAKS_PERCOBAAN = 5;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Mencoba login dengan batas 5 percobaan per menit per email + IP.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->pastikanTidakDibatasi();

        // Tanpa argumen kedua: fitur "ingat saya" sengaja tidak ada.
        if (! Auth::attempt($this->only('email', 'password'))) {
            if ($this->pembatasAktif()) {
                RateLimiter::hit($this->kunciPembatas());
            }

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->kunciPembatas());
    }

    // Dimatikan saat development (APP_ENV=local) atas permintaan pemilik proyek;
    // tetap aktif di produksi untuk mencegah brute force.
    private function pembatasAktif(): bool
    {
        return ! app()->isLocal();
    }

    /**
     * @throws ValidationException
     */
    private function pastikanTidakDibatasi(): void
    {
        if (! $this->pembatasAktif()
            || ! RateLimiter::tooManyAttempts($this->kunciPembatas(), self::MAKS_PERCOBAAN)) {
            return;
        }

        event(new Lockout($this));

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => RateLimiter::availableIn($this->kunciPembatas()),
            ]),
        ]);
    }

    private function kunciPembatas(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
