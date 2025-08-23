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
    case ACTIVE     = 'ACTIVE';      /* Fully active account */
    case INACTIVE   = 'INACTIVE';    /* Deactivated by user or admin */
    case BLOCKED    = 'BLOCKED';     /* Blocked due to violation, fraud, etc. */

    /**
     * Get the label for the user status.
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE     => 'Active',
            self::INACTIVE   => 'Inactive',
            self::BLOCKED    => 'Blocked',
        };
    }
}
