<?php

namespace App\Enums;

/*
 |----------------------------------------------------------------------
 | CompanyStatus enum (Variant B)
 |----------------------------------------------------------------------
 | Lifecycle for companies. Strings are persisted in DB (ENUM).
 | Keep string values stable to avoid DDL changes.
 |
 | Flow example:
 |  INCOMPLETE -> PENDING -> ACTIVE -> INACTIVE
 |  ACTIVE <-> SUSPENDED (temporary moderation pause)
 |
 | Licensing is handled by a separate model and must NOT be mixed here.
 */

enum CompanyStatus: string
{
    case INCOMPLETE = 'INCOMPLETE'; /* Profile missing required fields */
    case PENDING = 'PENDING';    /* Awaiting admin/owner approval */
    case ACTIVE = 'ACTIVE';     /* Fully active, can operate */
    case INACTIVE = 'INACTIVE';   /* Deactivated by admin/owner or user */
    case SUSPENDED = 'SUSPENDED';  /* Temporary suspension (moderation/audit) */

    /**
     * Get human-readable label for the status.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::INCOMPLETE => 'Incomplete',
            self::PENDING => 'Pending',
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::SUSPENDED => 'Suspended',
        };
    }

    /**
     * Check if the status is INCOMPLETE.
     *
     * @return bool
     */
    public function isIncomplete(): bool
    {
        return $this === self::INCOMPLETE;
    }

    /**
     * Check if the status is PENDING.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    /**
     * Check if the status is ACTIVE.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Check if the status is INACTIVE.
     *
     * @return bool
     */
    public function isInactive(): bool
    {
        return $this === self::INACTIVE;
    }

    /**
     * Check if the status is SUSPENDED.
     *
     * @return bool
     */
    public function isSuspended(): bool
    {
        return $this === self::SUSPENDED;
    }

    /**
     * Get all possible status values.
     *
     * @return array
     */
    public static function values(): array
    {
        return [
            self::INCOMPLETE->value,
            self::PENDING->value,
            self::ACTIVE->value,
            self::INACTIVE->value,
            self::SUSPENDED->value,
        ];
    }

    /**
     * Get all possible status options with labels.
     *
     * @return array
     */
    public static function options(): array
    {
        return [
            self::INCOMPLETE->value => self::INCOMPLETE->label(),
            self::PENDING->value => self::PENDING->label(),
            self::ACTIVE->value => self::ACTIVE->label(),
            self::INACTIVE->value => self::INACTIVE->label(),
            self::SUSPENDED->value => self::SUSPENDED->label(),
        ];
    }
}
