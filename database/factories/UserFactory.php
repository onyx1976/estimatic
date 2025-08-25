<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use App\Traits\HasPolishPhone;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * UserFactory
 *
 * Key features:
 * - Generates realistic Polish-centric user data.
 * - Uses a trait to generate valid Polish phone numbers.
 * - Includes states for roles (owner, admin, company) and statuses (active, inactive, blocked).
 * - Supports email verification states.
 */
class UserFactory extends Factory
{
    use HasPolishPhone;

    protected $model = User::class;


    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dateOfBirth = $this->faker->optional(0.7)->dateTimeBetween('-70 years', '-18 years');
        $emailVerifiedAt = $this->faker->optional(0.8)->dateTime();
        $lastLoginAt = $this->faker->optional(0.4)->dateTime();

        return [
            /* Role & Status */
            'role' => $this->faker->randomElement(array_column(UserRole::cases(), 'value')),
            'status' => $this->faker->randomElement(array_column(UserStatus::cases(), 'value')),

            /* Personal */
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'date_of_birth' => $dateOfBirth?->format('Y-m-d'),
            'gender' => $this->faker->optional()->randomElement(['male', 'female', 'other', 'prefer_not_to_say']),

            /* Contact & Auth */
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->optional(0.6)->phoneNumber(),
            'email_verified_at' => $emailVerifiedAt,
            'password' => Hash::make('password'),

            /* Profile */
            'avatar' => null,

            /* Localization & Preferences */
            'language' => 'pl',
            'locale' => 'pl_PL',
            'timezone' => 'Europe/Warsaw',
            'preferences' => $this->faker->optional() ? json_encode([
                'theme' => $this->faker->randomElement(['light', 'dark']),
                'notifications' => $this->faker->boolean(),
                'email_notifications' => $this->faker->boolean(80),
            ]) : null,

            /* Tracking */
            'last_login_at' => $lastLoginAt,
            'last_login_ip' => $this->faker->optional(0.4)->ipv4(),
            'login_failures' => $this->faker->numberBetween(0, 5),

            /* Auditing */
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    /**
     * Role States
     */

    /**
     * Set the user's role to 'owner'.
     *
     * @return self
     */
    public function owner(): self
    {
        /* Useful for quickly creating superusers in seeders/tests. */
        return $this->state(fn() => ['role' => UserRole::OWNER->value]);
    }

    /**
     * Set the user's role to 'admin'.
     *
     * @return self
     */
    public function admin(): self
    {
        return $this->state(fn() => ['role' => UserRole::ADMIN->value]);
    }

    /**
     * Set the user's role to 'company'.
     *
     * @return self
     */
    public function company(): self
    {
        return $this->state(fn() => ['role' => UserRole::COMPANY->value]);
    }

    /**
     * Status States
     */

    /**
     * Set the user's status to 'active'.
     *
     * @return self
     */
    public function active(): self
    {
        return $this->state(fn() => ['status' => UserStatus::ACTIVE->value]);
    }

    /**
     * Set the user's status to 'inactive'.
     *
     * @return self
     */
    public function inactive(): self
    {
        return $this->state(fn() => ['status' => UserStatus::INACTIVE->value]);
    }

    /**
     * Set the user's status to 'blocked'.
     *
     * @return self
     */
    public function blocked(): self
    {
        return $this->state(fn() => ['status' => UserStatus::BLOCKED->value]);
    }

    /**
     * Email verification states
     */

    /**
     * Mark the user's email as verified.
     *
     * @return self
     */
    public function verified(): self
    {
        return $this->state(fn() => ['email_verified_at' => now()]);
    }

    /**
     * Mark the user's email as unverified.
     *
     * @return self
     */
    public function unverified(): self
    {
        return $this->state(fn() => ['email_verified_at' => null]);
    }
}
