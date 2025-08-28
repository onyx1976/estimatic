<?php

namespace App\Http\Controllers\Auth;

use App\DTO\Auth\RegisterRequestDTO;
use App\Http\Controllers\BaseController;
use App\Services\Auth\RegisterService;
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
    public function store(Request $request, RegisterService $registerService): RedirectResponse
    {
        /* Minimal inline validation to keep UX predictable (Breeze-compatible) */
        $request->validate([
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8'],
            'name' => ['nullable', 'string', 'max:200'], /* Breeze fallback */
        ]);


        /* Build sanitized DTO (no hashing, no DB writes at this step) */
        $dto = RegisterRequestDTO::fromRequest($request);

        /* Call service preview to enforce domain defaults and read flags */
        $preview = $registerService->preview($dto);

        /* Safe breadcrumb (no PII); Service already logged masked data */
        Log::info('register.controller.preview', [
            'verify_first' => $preview['meta']['verify_first'] ?? null,
            'captcha_enabled' => $preview['meta']['captcha_enabled'] ?? null,
            'trial_days' => $preview['meta']['trial_days'] ?? null,
            'role' => $preview['user']['role'] ?? null,
        ]);

        /* For manual testing: flash a neutral status + non-sensitive flags */
        return back()
            ->withInput()
            ->with('status', 'register_preview_ok')
            ->with('register_preview_flags', [
                'verify_first' => $preview['meta']['verify_first'] ?? null,
                'captcha_enabled' => $preview['meta']['captcha_enabled'] ?? null,
                'trial_days' => $preview['meta']['trial_days'] ?? null,
                'role' => $preview['user']['role'] ?? null,
            ]);
    }
}
