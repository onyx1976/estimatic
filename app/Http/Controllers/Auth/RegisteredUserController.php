<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

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
        /* Log a breadcrumb to verify new path is hit and BaseController DI works */
        Log::info('register.store skeleton reached', [
            /* Read a real setting to confirm DI from BaseController works */
            'captcha_enabled' => $this->settings->getBool('security.captcha.enabled', true),
        ]);

        /* For now, just bounce back with a neutral status (no DB writes yet) */
        return back()->with('status', 'register_skeleton_reached');
    }
}
