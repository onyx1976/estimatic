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
        return [
            'first_name' => $dto->first_name,
            'last_name' => $dto->last_name,
            'email' => $dto->email,
            'password_plain' => $dto->password,
            'timezone' => $dto->timezone,
            'locale' => $dto->locale,
        ];
    }
}
