<?php

namespace App\Mappers\Auth;

use App\DTO\Auth\RegisterRequestDTO;

/**
 * Mapper for RegisterRequestDTO
 */
class RegisterMapper
{
    /**
     * Map RegisterRequestDTO into an array for future User creation.
     * NOTE: No hashing here, no DB writes, no side effects.
     */
    public static function toUserCreateArray(RegisterRequestDTO $dto): array
    {
        /* Base payload for User */
        $payload = [
            'first_name' => $dto->first_name,
            'last_name' => $dto->last_name,
            'email' => $dto->email,
            /* Password hashing will be applied in RegisterService later */
            'password_plain' => $dto->password,

            /* New fields persisted (users) */
            'timezone' => $dto->timezone, /* may be null */
            'locale' => $dto->locale,   /* may be null */
        ];

        /* Company data will be used when creating a Company draft (next steps) */
        $payload['company_name'] = $dto->company_name;

        /* Privacy consent can be recorded later in audit/consents table if needed */
        $payload['accept_privacy'] = $dto->accept_privacy;

        return $payload;
    }

    /**
     * Minimal payload to create Company draft at signup.
     * We only set company_name now; the full profile will be completed later.
     */
    public static function toCompanyCreateArray(RegisterRequestDTO $dto): array
    {
        /* Trim + cap defensively; FormRequest already validated size/regex */
        $name = trim($dto->company_name);
        return [
            'company_name' => mb_substr($name, 0, 200),
        ];
    }
}
