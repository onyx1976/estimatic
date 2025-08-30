<?php

namespace App\Services\Auth;

use App\DTO\Auth\RegisterRequestDTO;
use App\Enums\CompanyStatus;
use App\Mappers\Auth\RegisterMapper;
use App\Models\Company;
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
            'password_len' => isset($user['password_plain']) ? strlen((string) $user['password_plain']) : 0,
            'role' => $user['role'] ?? null,
            'verify_first' => $meta['verify_first'],
            'captcha_enabled' => $meta['captcha_enabled'],
        ]);

        return ['user' => $user, 'meta' => $meta];
    }

    /**
     * Create a new User and minimal Company draft from DTO.
     * * - Hashes password
     * * - Enforces role=COMPANY
     * * - Persists locale/timezone if provided
     * * - Creates Company with company_name only (profile completed later)
     * * - One DB transaction
     *
     * @return array{user: User, company: Company}
     * @param RegisterRequestDTO $dto
     * @throws Throwable
     */
    public function create(RegisterRequestDTO $dto): array
    {
        /* Map DTO → array (contains password_plain for now) */
        $payload = RegisterMapper::toUserCreateArray($dto);
        $payload['role'] = UserRole::COMPANY->value;

        /* Hash password and drop the plain value ASAP */
        $passwordPlain = (string) ($payload['password_plain'] ?? '');
        $payload['password'] = Hash::make($passwordPlain);
        unset($payload['password_plain']);

        /* Normalize email just in case */
        $payload['email'] = trim(strtolower($payload['email'] ?? ''));

        /* Build insert; include optional fields only if present/non-empty */
        $insertUser = [
            'first_name' => $payload['first_name'] ?? null,
            'last_name' => $payload['last_name'] ?? null,
            'email' => $payload['email'],
            'password' => $payload['password'] ?? null,
            'role' => $payload['role'] ?? UserRole::COMPANY->value,
        ];

        /* Persist locale/timezone if sent (DB already has defaults) */
        if (!empty($payload['locale'])) {
            $insertUser['locale'] = $payload['locale'];
        }
        if (!empty($payload['timezone'])) {
            $insertUser['timezone'] = $payload['timezone'];
        }

        /* Note: we do NOT set 'language' here; DB default 'pl' will be used.
            todo: If you want to derive it from locale later (e.g., 'pl_PL' → 'pl'), we can add it in a small follow-up. */

        /* Transactional create */
        [$user, $company] = DB::transaction(function () use ($insertUser, $dto) {
            /* 1) Create user */
            $user = User::query()->create($insertUser);

            /* 2) Create minimal company draft (company_name only) */
            $company = new Company();
            $company->user_id = $user->id;
            $company->company_name = trim($dto->company_name);
            $company->status = CompanyStatus::INCOMPLETE->value;

            $company->save();

            return [$user, $company];
        });

        Log::info('register.service.created_user', [
            'user_id' => $user->id,
            'email' => $this->maskEmail($user->email),
            'role' => $user->role,
            'locale' => $user->locale,
            'timezone' => $user->timezone,
            'company_id' => $company?->id,
        ]);

        return ['user' => $user];
    }

    /* ----------------------- Helpers ----------------------- */

    /** @internal mask email to first char + domain */
    protected function maskEmail(string $email): string
    {
        return (string) preg_replace('/(^.).*(@.*$)/', '$1***$2', $email);
    }
}
