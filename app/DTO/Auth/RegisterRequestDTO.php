<?php

namespace App\DTO\Auth;

use Illuminate\Http\Request;

/**
 * Data Transfer Object for Register Request
 */
class RegisterRequestDTO
{
    /* Keep properties public for simple mapping */
    public function __construct(
        public string $first_name,
        public string $last_name,
        public string $email,
        public string $password,
        public string $company_name,
        public bool $accept_privacy,
        public ?string $timezone = null,
        public ?string $locale = null,
    ) {
    }

    /**
     * Build DTO from HTTP request with minimal normalization.
     * Validation is handled by Form Request; we only sanitize and map fields.
     */
    public static function fromRequest(Request $request): self
    {
        /* Accept both our fields and Breeze default 'name' (split into first/last) */
        $first = trim((string) $request->input('first_name', ''));
        $last = trim((string) $request->input('last_name', ''));

        /* Normalize email: trim + lowercase */
        $email = strtolower(trim((string) $request->input('email', '')));

        /* Password: raw (hashing happens in service) */
        $password = (string) $request->input('password', '');

        /* Company name: trim */
        $company = trim((string) $request->input('company_name', ''));

        /* Privacy consent: cast to strict bool */
        $acceptPrivacy = filter_var($request->boolean('accept_privacy'), FILTER_VALIDATE_BOOLEAN);

        /* time_zone (request) → timezone (DTO) */
        $timezone = $request->filled('timezone')
            ? trim((string) $request->input('timezone'))
            : null;

        /* locale: keep as provided by hidden field (normalized on frontend) */
        $locale = $request->filled('locale')
            ? trim((string) $request->input('locale'))
            : null;

        return new self(
            first_name: $first,
            last_name: $last,
            email: $email,
            password: $password,
            company_name: $company,
            accept_privacy: $acceptPrivacy,
            timezone: $timezone,
            locale: $locale,
        );
    }

    /** Export to array for mappers/services */
    public function toArray(): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'password' => $this->password,
            'company_name' => $this->company_name,
            'accept_privacy' => $this->accept_privacy,
            'timezone' => $this->timezone,
            'locale' => $this->locale,
        ];
    }
}
