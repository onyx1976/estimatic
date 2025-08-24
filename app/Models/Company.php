<?php

namespace App\Models;

use App\Enums\CompanyStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Company model
 *
 * Key decisions:
 * - One-to-one relation with User (enforced via DB unique index on user_id).
 * - Status managed via CompanyStatus enum (includes INCOMPLETE, PENDING, ACTIVE, INACTIVE, SUSPENDED).
 * - Licensing is handled by a separate model (do NOT mix into CompanyStatus).
 * - Normalizing mutators (nip/regon/zipcode/phones/email/website) to keep DB clean.
 * - Helper methods for common status checks and simple profile completeness heuristic.
 * - Query scopes for common filtering/search patterns.
 *
 * @method static create(array $attributes)
 * @method static find(mixed $id)
 */
class Company extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Model configuration
     *
     * Keep fillable explicit and minimal. Email must be fillable for profile updates.
     *
     * @var list<string>
     */
    protected $fillable = [
        /* FK */
        'user_id',

        /* Identity */
        'company_name',
        'brand_name',
        'email',
        'phone',
        'phone_alt',

        /* Status */
        'status',

        /* Legal IDs */
        'nip',
        'regon',

        /* Address */
        'street',
        'building_no',
        'apartment_no',
        'city',
        'zipcode',
        'voivodeship',
        'country_code',

        /* Web & Media */
        'website',
        'logo_path',

        /* Meta */
        'meta',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * (None for now; consider moving sensitive PII elsewhere if needed.)
     *
     * @var list<string>
     */
    protected $hidden = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CompanyStatus::class,
            'meta' => 'array',
        ];
    }

    /**
     * The attributes that should be appended to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'full_address',
        'initials',
    ];

    /**
     * Relationships
     */

    /**
     * Owning user (1:1).
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        /* Role COMPANY is enforced at the application level (validators/services). */
        return $this->belongsTo(User::class);
    }

    /**
     * License relation placeholder.
     *
     * @return null
     */
    public function license(): null
    {
        /* Placeholder for future License model (one-to-one or one-to-many history). */
        return null;
    }

    /**
     * Accessors
     */

    /**
     * Computed human-readable address.
     *
     * @return string
     */
    public function getFullAddressAttribute(): string
    {
        /* Compose human-readable address skipping empty parts. */
        $parts = array_filter([
            trim((string) $this->street) ?: null,
            trim((string) $this->building_no) ?: null,
            trim((string) $this->apartment_no) ?: null,
        ]);

        $line1 = '';
        if (!empty($parts)) {
            /* e.g., "Długa 5/12" without assuming any prefix */
            $line1 = implode(' ', $parts);
        }

        $line2 = array_filter([
            $this->zipcode ? strtoupper($this->zipcode) : null,
            $this->city ? trim($this->city) : null,
        ]);
        $line2 = implode(' ', $line2);

        $country = $this->country_code ? strtoupper($this->country_code) : null;

        return trim(implode(', ', array_filter([$line1, $line2, $country])));
    }

    /**
     * Company initials (first letters of up to 2 words from brand/company name).
     *
     * @return string
     */
    public function getInitialsAttribute(): string
    {
        $source = trim($this->brand_name ?: $this->company_name ?: '');
        if ($source === '') {
            return '';
        }

        $words = preg_split('/\s+/', $source) ?: [];
        $initials = mb_strtoupper(mb_substr($words[0], 0, 1));
        if (isset($words[1])) {
            $initials .= mb_strtoupper(mb_substr($words[1], 0, 1));
        }
        return $initials;
    }

    /**
     * Classic Mutators
     * Normalize inputs to keep DB clean and comparisons reliable.
     */

    /**
     * Normalize email to lowercase and trim.
     *
     * @return void
     * @param mixed $value
     */
    public function setEmailAttribute(mixed $value): void
    {
        $this->attributes['email'] = $value !== null ? mb_strtolower(trim((string) $value)) : null;
    }

    /**
     * Strip spaces from primary phone.
     *
     * @return void
     * @param mixed $value
     */
    public function setPhoneAttribute(mixed $value): void
    {
        $this->attributes['phone'] = $value !== null
            ? preg_replace('/\s+/', '', trim((string) $value))
            : null;
    }

    /**
     * Strip spaces from secondary phone.
     *
     * @return void
     * @param mixed $value
     */
    public function setPhoneAltAttribute(mixed $value): void
    {
        $this->attributes['phone_alt'] = $value !== null
            ? preg_replace('/\s+/', '', trim((string) $value))
            : null;
    }

    /**
     * Keep only digits for NIP.
     *
     * @return void
     * @param mixed $value
     */
    public function setNipAttribute(mixed $value): void
    {
        $this->attributes['nip'] = $value !== null ? preg_replace('/\D+/', '', (string) $value) : null;
    }

    /**
     * Keep only digits for REGON.
     *
     * @return void
     * @param mixed $value
     */
    public function setRegonAttribute(mixed $value): void
    {
        $this->attributes['regon'] = $value !== null ? preg_replace('/\D+/', '', (string) $value) : null;
    }

    /**
     * Normalize PL zipcode to NN-NNN when 5 digits available.
     *
     * @return void
     * @param mixed $value
     */
    public function setZipcodeAttribute(mixed $value): void
    {
        if ($value === null) {
            $this->attributes['zipcode'] = null;
            return;
        }

        $digits = preg_replace('/\D+/', '', (string) $value);
        if (mb_strlen($digits) === 5) {
            $this->attributes['zipcode'] = mb_substr($digits, 0, 2).'-'.mb_substr($digits, 2);
        } else {
            $this->attributes['zipcode'] = trim((string) $value);
        }
    }

    /**
     * Normalize website URL (prepend https:// if scheme missing).
     *
     * @return void
     * @param mixed $value
     */
    public function setWebsiteAttribute(mixed $value): void
    {
        if ($value === null || trim((string) $value) === '') {
            $this->attributes['website'] = null;
            return;
        }

        $url = trim((string) $value);
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://'.$url;
        }
        $this->attributes['website'] = $url;
    }

    /**
     * Store ISO-3166-1 alpha-2 uppercase country code (default PL).
     *
     * @return void
     * @param mixed $value
     */
    public function setCountryCodeAttribute(mixed $value): void
    {
        $this->attributes['country_code'] = $value ? strtoupper((string) $value) : 'PL';
    }

    /**
     * Helpers — Status
     */

    /**
     * @return bool
     */
    public function isIncomplete(): bool
    {
        return $this->status === CompanyStatus::INCOMPLETE;
    }

    /**
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === CompanyStatus::PENDING;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === CompanyStatus::ACTIVE;
    }

    /**
     * @return bool
     */
    public function isInactive(): bool
    {
        return $this->status === CompanyStatus::INACTIVE;
    }

    /**
     * @return bool
     */
    public function isSuspended(): bool
    {
        return $this->status === CompanyStatus::SUSPENDED;
    }

    /**
     * Minimal completeness heuristic used by onboarding/status flows.
     *
     * @return bool
     */
    public function hasCompleteProfile(): bool
    {
        return filled($this->company_name)
            && filled($this->email)
            && filled($this->phone)
            && filled($this->city)
            && filled($this->zipcode);
    }

    /**
     * Query Scopes
     */

    /**
     * Scope: only ACTIVE companies.
     *
     * @return mixed
     * @param Builder $query
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', CompanyStatus::ACTIVE->value);
    }

    /**
     * Scope: LIKE-based search across common fields (portable across drivers).
     *
     * @return mixed
     * @param string|null $term
     * @param Builder $query
     */
    public function scopeSearch(Builder $query, ?string $term): mixed
    {
        if (blank($term)) {
            return $query;
        }

        $like = '%'.str_replace(' ', '%', $term).'%';
        return $query->where(function ($q) use ($like) {
            $q->where('company_name', 'like', $like)
                ->orWhere('brand_name', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('city', 'like', $like)
                ->orWhere('nip', 'like', $like);
        });
    }

    /**
     * Model events
     *
     * Ensure sane defaults at creation time.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(function (self $company) {
            $company->country_code ??= 'PL';

            /* Normalize email early for consistency with unique checks downstream. */
            if ($company->email) {
                $company->email = mb_strtolower(trim($company->email));
            }
        });
    }
}
