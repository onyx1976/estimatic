<?php

namespace App\Enums;

/**
 * Enum representing user roles.
 */
enum UserRole: string
{
    case OWNER = 'owner';
    case ADMIN = 'admin';
    case COMPANY = 'company';

    /**
     * Get the label for the user role.
     */
    public function label(): string
    {
        return match ($this) {
            self::OWNER => 'Owner',
            self::ADMIN => 'Admin',
            self::COMPANY => 'Company',
        };
    }

    /**
     * Get an array of privileged roles.
     */
    public static function privileged(): array
    {
        return [
            self::OWNER->value,
            self::ADMIN->value,
        ];
    }
}
