<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Setting model
 *
 * Key decisions:
 * - Use of enums for role and status for type safety and consistency.
 * - Soft deletes to allow recovery of deleted users.
 * - Rich set of fields for personal info, contact, localization, preferences, and tracking.
 * - Helper methods for common role/status checks to keep controllers/services clean.
 * - Query scopes for common queries (active users, users by role).
 * @method static create(array $array)
 * @method static find(mixed $id)
 */
class Setting extends Model
{
    /* Table name is plural by convention; keep explicit for clarity */
    protected $table = 'settings';

    /* Mass assignment whitelist to avoid accidental writes */
    protected $fillable = ['key', 'value', 'type'];

    /* Cast JSON column to array for convenient access */
    protected $casts = [
        'value' => 'array',
    ];
}
