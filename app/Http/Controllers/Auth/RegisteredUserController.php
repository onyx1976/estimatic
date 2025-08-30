<?php

namespace App\Http\Controllers\Auth;

use App\DTO\Auth\RegisterRequestDTO;
use App\Events\CompanyProfileCreated;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\RegisterService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\AuthenticationException;
use Throwable;

class RegisteredUserController extends BaseController
{
    public function create()
    {
        return view('auth.register');
    }

    /**
     * @throws AuthenticationException
     */
    public function store(RegisterRequest $request, RegisterService $registerService): RedirectResponse
    {
        $dto = RegisterRequestDTO::fromRequest($request);

        try {
            $result = $registerService->create($dto);
            $user   = $result['user'];
        } catch (QueryException $e) {
            /* Log full SQLSTATE + driver code to diagnose real cause (company insert etc.) */
            Log::warning('register.store query_exception', [
                'sqlstate'    => $e->errorInfo[0] ?? null,
                'driver_code' => $e->errorInfo[1] ?? null,
                'message'     => $e->getMessage(),
            ]);

            /* Only show "email taken" if it's truly a unique(users.email) violation */
            if ($this->isUniqueEmailViolation($e)) {
                return back()->withInput()
                    ->withErrors(['email' => __('This email is already taken.')]);
            }

            return back()->withInput()
                ->with('status', 'register_failed'); /* neutral message for other DB errors */
        } catch (Throwable $e) {
            Log::error('register.store failed', ['ex' => $e->getMessage()]);
            return back()->withInput()->with('status', 'register_failed');
        }

        try {
            Auth::login($user);
        } catch (Throwable $e) {
            Log::error('register.auth.login_failed', ['user_id' => $user->id, 'ex' => $e->getMessage()]);
            throw new AuthenticationException('Login failed after registration.');
        }

        Log::info('register.controller.user_created', ['user_id' => $user->id]);
        event(new CompanyProfileCreated($user));

        return redirect()->route('verification.notice');
    }

    /* ----------------------- helpers ----------------------- */

    /* Detect a true unique constraint on users.email (MySQL/Postgres) */
    private function isUniqueEmailViolation(QueryException $e): bool
    {
        $sqlState   = $e->errorInfo[0] ?? null;      // e.g. '23000' (SQLSTATE class integrity constraint)
        $driverCode = (int)($e->errorInfo[1] ?? 0);  // MySQL 1062 duplicate key
        $msg        = $e->getMessage();

        /* MySQL/MariaDB duplicate key */
        if ($sqlState === '23000' && $driverCode === 1062 && str_contains($msg, 'users') && str_contains($msg, 'email')) {
            return true;
        }

        /* PostgreSQL unique_violation */
        if ($sqlState === '23505' && (str_contains($msg, 'users_email_unique') || (str_contains($msg, 'users') && str_contains($msg, 'email')))) {
            return true;
        }

        /* Fallback heuristic */
        return str_contains($msg, 'unique') && str_contains($msg, 'users') && str_contains($msg, 'email');
    }
}
