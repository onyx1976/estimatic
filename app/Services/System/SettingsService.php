<?php

namespace App\Services\System;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class SettingsService
{
    /* Simple per-request cache to avoid repeated queries */
    protected array $cache = [];

    /* Generic getter with safe fallback */
    public function get(string $key, mixed $default = null): mixed
    {
        /* Return from local cache if present */
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        /* Fetch from DB; missing -> default */
        $row = Setting::query()->where('key', $key)->first();
        $value = $row?->value ?? $default;

        /* Cache and return */
        return $this->cache[$key] = $value;
    }

    public function getBool(string $key, bool $default = false): bool
    {
        $val = $this->get($key, $default);

        /* Coerce common truthy/falsy representations */
        if (is_string($val)) {
            $v = strtolower(trim($val));
            if (in_array($v, ['true', '1', 'yes', 'on'], true)) {
                return true;
            }
            if (in_array($v, ['false', '0', 'no', 'off', ''], true)) {
                return false;
            }
        }
        return (bool) $val;
    }

    public function getInt(string $key, int $default = 0): int
    {
        $val = $this->get($key, $default);
        if (!is_numeric($val)) {
            return $default;
        }

        $num = (int) $val;

        /* Domain guards for sensitive keys */
        if ($key === 'trial.duration_days') {
            if ($num < 7 || $num > 60) {
                Log::info("Settings: trial.duration_days out of range, fallback=14");
                return 14;
            }
        }

        if ($key === 'security.signup.throttle_per_ip') {
            if ($num < 1) {
                return 0;
            }   /* 0 = disabled */
            if ($num > 50) {
                return 50;
            } /* upper bound */
        }

        return $num;
    }

    public function getString(string $key, ?string $default = null): ?string
    {
        $val = $this->get($key, $default);
        $val = is_string($val) ? trim($val) : $val;

        /* Safe defaults for localization keys */
        if (in_array($key, ['defaults.language', 'defaults.locale', 'defaults.timezone'], true)) {
            if (!$val) {
                return match ($key) {
                    'defaults.language' => 'pl',
                    'defaults.locale' => 'pl_PL',
                    'defaults.timezone' => 'Europe/Warsaw',
                    default => $default,
                };
            }
        }

        return is_string($val) ? $val : $default;
    }

    public function getArray(string $key, array $default = []): array
    {
        $val = $this->get($key, $default);
        if (!is_array($val)) {
            return $default;
        }

        /* Whitelist prefixes for onboarding fields */
        if (in_array($key, ['onboarding.required_fields.user', 'onboarding.required_fields.company'], true)) {
            return array_values(array_filter($val, function ($entry) {
                return is_string($entry) && (
                        str_starts_with($entry, 'user.') || str_starts_with($entry, 'company.')
                    );
            }));
        }

        /* Ensure array of non-empty strings for denylist domains */
        if ($key === 'security.email.disposable_denylist.domains') {
            return array_values(array_filter($val, fn($d) => is_string($d) && $d !== ''));
        }

        return $val;
    }
}
