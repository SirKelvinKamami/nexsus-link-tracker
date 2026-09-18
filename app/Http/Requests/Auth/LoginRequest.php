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
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ];
    }

    public function authenticate()
    {
        $this->ensureIsNotRateLimited();

        if (RateLimiter::attempts($this->throttleKey()) >= 3) {
            $expected = $this->session()->get('login_captcha_answer');
            if ($expected === null) {
                $a = random_int(1, 9);
                $b = random_int(1, 9);
                $this->session()->put('login_captcha_answer', $a + $b);
                $this->session()->put('login_captcha_question', "$a + $b = ?");
                throw ValidationException::withMessages([
                    'captcha' => __('Please solve the CAPTCHA: ') . $this->session()->get('login_captcha_question'),
                ]);
            }
            if (!$this->filled('captcha') || (string) $this->input('captcha') !== (string) $expected) {
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'captcha' => __('Please solve the CAPTCHA correctly.'),
                ]);
            }
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password, 'block' => 'no'] , $this->filled('remember'))) {
            RateLimiter::hit($this->throttleKey());
            $a = random_int(1, 9);
            $b = random_int(1, 9);
            $this->session()->put('login_captcha_answer', $a + $b);
            $this->session()->put('login_captcha_question', "$a + $b = ?");

            throw ValidationException::withMessages([
                'email' => __('messages.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        $this->session()->forget(['login_captcha_answer', 'login_captcha_question']);
    }

    public function ensureIsNotRateLimited()
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 10)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey()
    {
        return Str::lower($this->input('email')).'|'.$this->ip();
    }
}
