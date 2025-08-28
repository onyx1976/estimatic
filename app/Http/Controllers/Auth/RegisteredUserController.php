<?php

namespace App\Http\Controllers\Auth;

use App\DTO\Auth\RegisterRequestDTO;
use App\Http\Controllers\BaseController;
use App\Services\Auth\RegisterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Throwable;

class RegisteredUserController extends BaseController
{
    public function create()
    {
        /* Later we may pass settings-based flags to the view */
        return view('auth.register');
    }

    public function store(Request $request, RegisterService $registerService)
    {
        /* Breeze-like validation; keep UX predictable */
        $request->validate([
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
//            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'name' => ['nullable', 'string', 'max:200'], /* Breeze fallback */
        ]);

        /* Build sanitized DTO (no side effects) */
        $dto = RegisterRequestDTO::fromRequest($request);

        try {
            /* Create user (hash inside service). No auto-login, no mail yet. */
            $result = $registerService->create($dto);
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
                ->with('status', 'register_failed'); /* neutral UX */
        }

        /* Success: stay neutral; mail & verify flow will be added next */
        Log::info('register.controller.user_created', ['user_id' => $result['user']->id]);

        return redirect()
            ->route('register')/* back to form for now */
            ->with('status', 'register_user_created'); /* temporary flash */
    }
}
