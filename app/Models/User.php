<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * User model
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
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     *  Model configuration
     *
     *  Keep fillable minimal and explicit. Sensitive fields are hidden.
     *  Enum casting guarantees type-safety across controllers/services.
     */
    protected $fillable = [
        /* Personal */
        'first_name', 'last_name', 'date_of_birth', 'gender',
        /* Contact & Auth */
        'email', 'phone', 'password',
        /* Profile */
        'avatar',
        /* Role & Status */
        'role', 'status',
        /* Localization & Preferences */
        'language', 'locale', 'timezone', 'preferences',
        /* Tracking */
        'last_login_at', 'last_login_ip', 'login_failures',
        /* Auditing */
        'created_by', 'updated_by',
        /* Email verification is set by framework but allow manual seeding */
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * Hide sensitive attributes from arrays/JSON.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'status' => UserStatus::class,
            'date_of_birth' => 'date',
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'preferences' => 'array',
        ];
    }

    /**
     * The attributes that should be appended to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'full_name',
    ];


    /**
     * Relationships.
     */
    public function company(): HasOne
    {
        /* Related company profile (1:1); role COMPANY enforced at application/service level */
        return $this->hasOne(Company::class);
    }

    /**
     * Helpers Methods - Roles
     * Prefer enum-aware checks everywhere instead of string comparisons.
     */

    /**
     * Check if the user has an owner role.
     *
     * @return bool
     */
    public function isOwner(): bool
    {
        return $this->role === UserRole::OWNER;
    }

    /**
     * Check if the user has an admin role.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    /**
     * Check if the user has a company role.
     *
     * @return bool
     */
    public function isCompany(): bool
    {
        return $this->role === UserRole::COMPANY;
    }

    /**
     * Check if the user has a privileged role (owner or admin).
     *
     * @return bool
     * @param UserRole|string $role
     */
    public function hasRole(UserRole|string $role): bool
    {
        return $this->role === ($role instanceof UserRole ? $role->value : strtolower($role));
    }

    /**
     * Check if the user has any of the given roles.
     *
     * @return bool
     * @param array<UserRole|string> $roles
     */
    public function hasAnyRole(array $roles): bool
    {
        $userRole = strtolower($this->role->value); // enum → string

        $normalizedRoles = array_map(static function (UserRole|string $role) {
            return strtolower($role instanceof UserRole ? $role->value : (string) $role);
        }, $roles);

        return in_array($userRole, $normalizedRoles, true);
    }

    /**
     * Helpers Methods - Status
     * Prefer enum-aware checks everywhere instead of string comparisons.
     */

    /**
     * Check if the user is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === UserStatus::ACTIVE;
    }

    /**
     * Check if the user is inactive.
     *
     * @return bool
     */
    public function isInactive(): bool
    {
        return $this->status === UserStatus::INACTIVE;
    }

    /**
     * Check if the user is blocked.
     *
     * @return bool
     */
    public function isBlocked(): bool
    {
        return $this->status === UserStatus::BLOCKED;
    }

    /**
     * Check if the user has complete profile.
     *
     * @return bool
     */
    public function hasCompleteProfile(): bool
    {
        return filled($this->first_name)
            && filled($this->last_name)
            && filled($this->email)
            && filled($this->phone);
    }

    /**
     * Query Scopes
     */

    /**
     * Scope a query to only include active users.
     *
     * @return mixed
     * @param $query
     */
    public function scopeActive($query): mixed
    {
        return $query->where('status', UserStatus::ACTIVE->value);
    }

    /**
     * Scope a query to only include users of a given role.
     *
     * @return mixed
     * @param $query
     * @param UserRole|string $role
     */
    public function scopeOfRole($query, UserRole|string $role): mixed
    {
        $value = $role instanceof UserRole ? $role->value : UserRole::from($role)->value;
        return $query->where('role', $value);
    }
}
