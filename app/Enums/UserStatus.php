<?php

namespace App\Enums;

/*
 |----------------------------------------------------------------------
 | UserStatus enum
 |----------------------------------------------------------------------
 | Application-wide user lifecycle states, stored as DB ENUM.
 | Keep string values stable — they are persisted in the database.
 |
 */

enum UserStatus: string
{
    case ACTIVE = 'ACTIVE';      /* Fully active account */
    case INACTIVE = 'INACTIVE';    /* Deactivated by user or admin */
    case BLOCKED = 'BLOCKED';     /* Blocked due to violation, fraud, etc. */

    /**
     * Get a human-readable label for the enum value.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::BLOCKED => 'Blocked',
        };
    }

    /**
     * Get all possible enum values as an array of strings.
     *
     * @return array
     */
    public static function values(): array
    {
        return [
            self::ACTIVE->value,
            self::INACTIVE->value,
            self::BLOCKED->value,
        ];
    }
}
