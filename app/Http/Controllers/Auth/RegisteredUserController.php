<?php

namespace App\Http\Controllers\Auth;

use App\DTO\Auth\RegisterRequestDTO;
use App\Http\Controllers\BaseController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Controller for user registration
 */
class RegisteredUserController extends BaseController
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        /* In next steps we may pass flags (captcha, throttle) read from settings */
        return view('auth.register');
    }

    /* Skeleton: do not create user yet; only prove the wire-up works */
    public function store(Request $request): RedirectResponse
    {
        /* Minimal inline validation to keep UX predictable (Breeze-compatible) */
        $request->validate([
            /* Our fields */
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255'],
//            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8'],

            /* Breeze fallback: allow 'name' if UI still posts it */
            'name' => ['nullable', 'string', 'max:200'],
        ]);

        /* Build sanitized DTO (no hashing, no DB writes at this step) */
        $dto = RegisterRequestDTO::fromRequest($request);

        /* Read a real feature flag to prove BaseController DI works */
        $captchaEnabled = $this->settings->getBool('security.captcha.enabled', true);

        /* Privacy-conscious logging: mask PII, never log password value */
        $emailMasked = preg_replace('/(^.).*(@.*$)/', '$1***$2', $dto->email);
        $phoneMasked = strlen($dto->phone) > 2
            ? str_repeat('*', max(strlen($dto->phone) - 2, 0)).substr($dto->phone, -2)
            : $dto->phone;

        Log::info('register.store skeleton (DTO wired)', [
            /* Do not log names to avoid PII spill */
            'email' => $emailMasked,
            'phone' => $phoneMasked,
            'password_len' => strlen($dto->password), /* password length only */
            'captcha_enabled' => $captchaEnabled,
        ]);

        /* Next steps will call RegisterService + Mapper here */
        /* For now, bounce back with status so we can verify the flow manually */
        return back()
            ->withInput()
            ->with('status', 'register_dto_ok'); /* client-ready, no sensitive details */
    }
}
