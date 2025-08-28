<?php

namespace App\Services\Auth;

use App\DTO\Auth\RegisterRequestDTO;
use App\Mappers\Auth\RegisterMapper;
use App\Services\System\SettingsService;
use Illuminate\Support\Facades\Log;
use App\Enums\UserRole;

/**
 * Service for user registration
 */
class RegisterService
{
    public function __construct(
        protected SettingsService $settings
    ) {
    }

    /**
     * Build a safe payload from DTO for future user creation.
     * No hashing, no DB writes, no side effects beyond a privacy-safe log.
     */
    public function preview(RegisterRequestDTO $dto): array
    {
        /* Map DTO → array; keep password in a temporary field for later hashing */
        $user = RegisterMapper::toUserCreateArray($dto);

        /* Enforce domain defaults (public sign-up is COMPANY) */
        $user['role'] = UserRole::COMPANY->value; /* ignore any incoming role */

        /* Do NOT set User.status here.
           - INCOMPLETE is a COMPANY notion (company profile completeness).
           - User gating is handled by EnsureEmailVerified + RedirectIfIncomplete.
           - UserStatus::BLOCKED is moderation-only and never set at signup.
        */

        /* Read feature flags we will use later in the flow */
        $meta = [
            'verify_first' => $this->settings->getBool('auth.verify_first', true),
            'captcha_enabled' => $this->settings->getBool('security.captcha.enabled', true),
            'trial_deferred' => $this->settings->getBool('trial.defer_until_profile_complete', true),
            'trial_days' => $this->settings->getInt('trial.duration_days', 14),
        ];

        /* Privacy-conscious breadcrumb (never log raw password or full PII) */
        Log::info('register.service.preview', [
            'email' => $this->maskEmail($user['email'] ?? ''),
            'phone' => $this->maskPhone($user['phone'] ?? ''),
            'password_len' => isset($user['password_plain']) ? strlen((string) $user['password_plain']) : 0,
            'role' => $user['role'] ?? null,
            /* Removed: 'status' log */
            'verify_first' => $meta['verify_first'],
            'captcha_enabled' => $meta['captcha_enabled'],
        ]);

        return [
            'user' => $user, /* contains password_plain for the next step (hashing) */
            'meta' => $meta,
        ];
    }

    /* ----------------------- Helpers ----------------------- */

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
