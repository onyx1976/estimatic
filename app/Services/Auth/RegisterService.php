<?php

namespace App\Services\Auth;

use App\DTO\Auth\RegisterRequestDTO;
use App\Mappers\Auth\RegisterMapper;
use App\Services\System\SettingsService;
use App\Enums\UserRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Throwable;

class RegisterService
{
    public function __construct(
        protected SettingsService $settings
    ) {
    }

    /**
     * Preview-only (kept from previous step) – no DB writes.
     */
    public function preview(RegisterRequestDTO $dto): array
    {
        $user = RegisterMapper::toUserCreateArray($dto);
        $user['role'] = UserRole::COMPANY->value; /* public sign-up is COMPANY */

        $meta = [
            'verify_first' => $this->settings->getBool('auth.verify_first', true),
            'captcha_enabled' => $this->settings->getBool('security.captcha.enabled', true),
            'trial_deferred' => $this->settings->getBool('trial.defer_until_profile_complete', true),
            'trial_days' => $this->settings->getInt('trial.duration_days', 14),
        ];

        Log::info('register.service.preview', [
            'email' => $this->maskEmail($user['email'] ?? ''),
            'phone' => $this->maskPhone($user['phone'] ?? ''),
            'password_len' => isset($user['password_plain']) ? strlen((string) $user['password_plain']) : 0,
            'role' => $user['role'] ?? null,
            'verify_first' => $meta['verify_first'],
            'captcha_enabled' => $meta['captcha_enabled'],
        ]);

        return ['user' => $user, 'meta' => $meta];
    }

    /**
     * Create a new User record from DTO.
     * - Hashes password
     * - Enforces role=COMPANY (ignores any incoming role)
     * - Runs in DB transaction
     * - NO mail sending here (next step)
     *
     * @return array{user: User}
     * @param RegisterRequestDTO $dto
     * @throws Throwable
     */
    public function create(RegisterRequestDTO $dto): array
    {
        /* Map DTO → array (contains password_plain for now) */
        $payload = RegisterMapper::toUserCreateArray($dto);

        /* Enforce domain defaults */
        $payload['role'] = UserRole::COMPANY->value;

        /* Hash password and drop the plain value ASAP */
        $passwordPlain = (string) ($payload['password_plain'] ?? '');
        $payload['password'] = Hash::make($passwordPlain);
        unset($payload['password_plain']);

        /* Optional: additional normalization guards (email already lowercased in DTO) */
        $payload['email'] = trim(strtolower($payload['email'] ?? ''));

        /* Minimal safety: never accept status/role from outside mapper */
        unset($payload['status']); /* company completeness handled elsewhere */

        /* Transactional create */
        $user = DB::transaction(function () use ($payload) {
            /* Only pass whitelisted keys that exist in User::$fillable */
            $insert = [
                'first_name' => $payload['first_name'] ?? null,
                'last_name' => $payload['last_name'] ?? null,
                'email' => $payload['email'] ?? null,
                'phone' => $payload['phone'] ?? null,
                'password' => $payload['password'] ?? null,
                'role' => $payload['role'] ?? UserRole::COMPANY->value,
                /* Any other defaults (locale/timezone) set elsewhere if needed */
            ];

            return User::query()->create($insert);
        });

        Log::info('register.service.created_user', [
            'user_id' => $user->id,
            'email' => $this->maskEmail($user->email),
            'role' => $user->role,
        ]);

        return ['user' => $user];
    }

    /* ----------------------- helpers ----------------------- */

    /** @internal mask email to first char + domain */
    protected function maskEmail(string $email): string
    {
        return (string) preg_replace('/(^.).*(@.*$)/', '$1***$2', $email);
    }

    /** @internal keep only last 2 digits */
    protected function maskPhone(string $phone): string
    {
        $len = strlen($phone);
        if ($len <= 2) {
            return $phone;
        }
        return str_repeat('*', $len - 2).substr($phone, -2);
    }
}
