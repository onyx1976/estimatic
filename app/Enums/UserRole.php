<?php

namespace App\Enums;

/*
 |----------------------------------------------------------------------
 | UserRole enum
 |----------------------------------------------------------------------
 | Source of truth for user roles across the application.
 | Keep the string values stable — they are persisted in the DB (ENUM).
 | Used by:
 | - users table migration (array_column(UserRole::cases(), 'value'))
 | - authorization checks and policies
 | - form options / filters
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
