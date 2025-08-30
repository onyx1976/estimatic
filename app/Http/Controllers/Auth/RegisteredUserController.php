<?php

namespace App\Http\Controllers\Auth;

use App\DTO\Auth\RegisterRequestDTO;
use App\Events\CompanyProfileCreated;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\RegisterService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Throwable;

class RegisteredUserController extends BaseController
{
    public function create()
    {
        /* todo: Later we may pass settings-based flags to the view */
        return view('auth.register');
    }

    /**
     * @throws AuthenticationException
     */
    public function store(RegisterRequest $request, RegisterService $registerService): RedirectResponse
    {

        /* Build sanitized DTO (no side effects) */
        $dto = RegisterRequestDTO::fromRequest($request);

        try {

            /* Create user via service (hash inside) */
            $result = $registerService->create($dto);
            $user = $result['user'];

        } catch (QueryException $e) {

            /* Handle race-condition on unique email (neutral message) */
            Log::warning('register.store unique constraint', ['code' => $e->getCode()]);
            return back()
                ->withInput()
                ->withErrors(['email' => __('This email is already taken.')]);
        } catch (Throwable $e) {

            /* Privacy-safe log; do not leak PII to user */
            Log::error('register.store failed', ['ex' => $e->getMessage()]);
            return back()
                ->withInput()
                ->with('status', 'register_failed');
        }

        /* Immediate login (your chosen flow) */
        try {
            Auth::login($user);
        } catch (Throwable $e) {
            /* Extremely rare; keep user created but show neutral message */
            Log::error('register.auth.login_failed', ['user_id' => $user->id, 'ex' => $e->getMessage()]);
            throw new AuthenticationException('Login failed after registration.');
        }

        /* Success: stay neutral; mail & verify flow will be added next */
        Log::info('register.controller.user_created', ['user_id' => $result['user']->id]);

        /* Fire onboarding event – listeners will create Company draft/profile */
        event(new CompanyProfileCreated($user));

        /* Redirect to onboarding */
        return redirect()->route('verification.notice');
    }
}
