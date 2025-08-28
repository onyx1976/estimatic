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
        public string $phone,
        public string $password
    ) {
    }

    /**
     * Build DTO from HTTP request with minimal normalization.
     * We DO NOT validate here (validation stays in controller/request); just sanitize.
     */
    public static function fromRequest(Request $request): self
    {
        /* Accept both our fields and Breeze default 'name' (split into first/last) */
        $first = trim((string) $request->input('first_name', ''));
        $last = trim((string) $request->input('last_name', ''));

        if ($first === '' && $last === '') {
            /* Try to split Breeze 'name' into first/last by the last space */
            $full = trim((string) $request->input('name', ''));
            if ($full !== '') {
                $parts = preg_split('/\s+/', $full);
                $last = array_pop($parts) ?? '';
                $first = trim(implode(' ', $parts));
                if ($first === '') {
                    $first = $last;
                    $last = '';
                }
            }
        }

        /* Normalize email: trim + lowercase */
        $email = strtolower(trim((string) $request->input('email', '')));

        /* Normalize phone to E.164-like: keep only + and digits */
        $rawPhone = (string) $request->input('phone', '');
        $phone = preg_replace('/[^+\d]/', '', $rawPhone) ?? '';

        /* Password as given (no hashing here) */
        $password = (string) $request->input('password', '');

        return new self($first, $last, $email, $phone, $password);
    }

    /** Export to array for mappers/services */
    public function toArray(): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'password' => $this->password,
        ];
    }
}
