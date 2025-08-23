<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use App\Traits\HasPolishPhone;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
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
        /* Polish first names for more realistic data */
        $maleNames = ['Jan', 'Piotr', 'Krzysztof', 'Andrzej', 'Tomasz', 'Paweł', 'Michał', 'Marcin'];
        $femaleNames = ['Anna', 'Maria', 'Katarzyna', 'Małgorzata', 'Agnieszka', 'Barbara', 'Ewa', 'Magdalena'];
        $firstNames = array_merge($maleNames, $femaleNames);

        /* Polish last names */
        $lastNames = ['Nowak', 'Kowalski', 'Wiśniewski', 'Dąbrowski', 'Lewandowski', 'Wójcik', 'Kamiński', 'Kowalczyk'];


        return [
            /* Personal */
            'first_name' => $this->faker->randomElement($firstNames),
            'last_name' => $this->faker->randomElement($lastNames),
            'date_of_birth' => $this->faker->optional()->date(),
            'gender' => $this->faker->randomElement(['male', 'female', 'other', 'prefer_not_to_say']),

            /* Contact & Auth */
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->generatePolishPhone(),
            'email_verified_at' => $this->faker->optional(0.7)->dateTimeBetween('-30 days'),
            'password' => Hash::make('password'),

            /* Profile */
            'avatar' => null,

            /* Role & Status */
            'role' => $this->faker->randomElement(UserRole::values()),
            'status' => $this->faker->randomElement([
                UserStatus::ACTIVE->value,
                UserStatus::INACTIVE->value,
                UserStatus::BLOCKED->value,
            ]),

            /* Localization & Preferences */
            'language' => 'pl',
            'locale' => 'pl_PL',
            'timezone' => 'Europe/Warsaw',
            'preferences' => ['notifications' => ['email' => true, 'sms' => false]],

            /* Tracking */
            'last_login_at' => $this->faker->optional()->dateTimeBetween('-15 days'),
            'last_login_ip' => $this->faker->optional()->ipv4(),
            'login_failures' => 0,

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
