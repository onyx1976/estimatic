<?php

namespace App\Enums;

/*
 |----------------------------------------------------------------------
 | UserStatus enum
 |----------------------------------------------------------------------
 | Application-wide user lifecycle states, stored as DB ENUM.
 | Keep string values stable — they are persisted in the database.
 |
 | Typical flow:
 |   INCOMPLETE -> PENDING (optional) -> ACTIVE -> INACTIVE
 |   Any status can be forced to BLOCK by admin/system.
 */
enum UserStatus: string
{
    case INCOMPLETE = 'INCOMPLETE';  /* Profile not complete yet */
    case PENDING    = 'PENDING';     /* Awaiting review/activation */
    case ACTIVE     = 'ACTIVE';      /* Fully active account */
    case INACTIVE   = 'INACTIVE';    /* Deactivated by user or admin */
    case BLOCKED    = 'BLOCKED';     /* Blocked due to violation, fraud, etc. */

    /**
     * Get the label for the user status.
     */
    public function label(): string
    {
        return match ($this) {
            self::INCOMPLETE => 'Incomplete',
            self::PENDING    => 'Pending',
            self::ACTIVE     => 'Active',
            self::INACTIVE   => 'Inactive',
            self::BLOCKED    => 'Blocked',
        };
    }
}
