<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        /* Helper to upsert without overwriting existing values */
        $put = function (string $key, mixed $value, ?string $type = null): void {
            $existing = Setting::query()->where('key', $key)->first();
            if ($existing) {
                return;
            } /* idempotent: do not overwrite */
            Setting::query()->create([
                'key' => $key,
                'value' => $value,
                'type' => $type,
            ]);
        };

        /* A) Auth / Verify */
        $put('auth.verify_first', true, 'bool');
        $put('mail.verify.throttle_seconds', 60, 'int');

        /* B) Onboarding required fields */
        $put('onboarding.required_fields.user', [
            'user.first_name', 'user.last_name', 'user.email', 'user.phone',
        ], 'array');

        $put('onboarding.required_fields.company', [
            'company.company_name', 'company.nip', 'company.street', 'company.building_no',
            'company.city', 'company.zipcode', 'company.country_code',
        ], 'array');

        /* C) Trial / licensing */
        $put('trial.duration_days', 14, 'int');
        $put('trial.defer_until_profile_complete', true, 'bool');

        /* D) Security */
        $put('security.captcha.enabled', true, 'bool');
        $put('security.signup.throttle_per_ip', 5, 'int');
        $put('security.signup.honeypot.enabled', true, 'bool');
        $put('security.signup.timelock.min_seconds', 3, 'int');
        $put('security.email.disposable_denylist.enabled', true, 'bool');
        $put('security.email.disposable_denylist.domains', [], 'array');

        /* E) Defaults */
        $put('defaults.language', 'pl', 'string');
        $put('defaults.locale', 'pl_PL', 'string');
        $put('defaults.timezone', 'Europe/Warsaw', 'string');

        /* F) Feature flags */
        $put('feature.preview.enabled', false, 'bool');
    }
}
