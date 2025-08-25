<?php

namespace Database\Factories;

use App\Enums\CompanyStatus;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/*
 |----------------------------------------------------------------------
 | CompanyFactory
 |----------------------------------------------------------------------
 | Generates valid companies linked 1:1 with a COMPANY user.
 | - Uses a safe picker to avoid Faker::randomElement edge cases.
 | - Includes handy states for lifecycle: incomplete/pending/active/inactive/suspended.
 | - Keeps data PL-centric (zipcode, country_code).
 */

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        $companyName = $this->faker->company();
        $brand = $this->faker->optional()->companySuffix(); /* simple “brand” flavor */

        return [
            /* 1:1 owner – creates a matching COMPANY user if not provided */
            'user_id' => User::factory()->company()->active(),

            /* Status */
            'status' => $this->faker->randomElement(CompanyStatus::values()),

            /* Identity */
            'company_name' => $this->faker->company(),
            'brand_name' => $this->faker->optional(0.4)->word().($brand ? ' '.$brand : ''),

            /* Contact */
            'email' => $this->faker->optional(0.8)->companyEmail(),
            'phone' => $this->faker->optional(0.9)->phoneNumber(),
            'phone_alt' => $this->faker->optional(0.3)->phoneNumber(),

            /* Legal IDs (nullable unique in DB; keep optional to avoid collisions) */
            'nip' => $this->faker->optional(0.6)->numerify('##########'),   /* 10 digits */
            'regon' => $this->faker->optional()->numerify('#########'),    /* 9 digits */

            /* Address (PL) */
            'street' => $this->faker->optional()->streetName(),
            'building_no' => $this->faker->optional()->buildingNumber(),
            'apartment_no' => $this->faker->optional(0.3)->numberBetween(1, 199),
            'city' => $this->faker->optional()->city(),
            'zipcode' => $this->faker->optional()->numerify('##-###'),
            'voivodeship' => $this->faker->optional(0.8)->randomElement([
                'dolnośląskie', 'kujawsko-pomorskie', 'lubelskie', 'lubuskie',
                'łódzkie', 'małopolskie', 'mazowieckie', 'opolskie',
                'podkarpackie', 'podlaskie', 'pomorskie', 'śląskie',
                'świętokrzyskie', 'warmińsko-mazurskie', 'wielkopolskie', 'zachodniopomorskie'
            ]),
            'country_code' => 'PL',

            /* Web & Media */
            'website' => $this->faker->optional()->url(),
            'logo_path' => null,

            /* Meta */
            'meta' => $this->faker->optional(0.3)->randomElements(
                ['paving', 'driveways', 'patios', 'industrial', 'public'],
                $this->faker->numberBetween(1, 3)
            ),
        ];
    }

    /* ------------------------------
     | Status states
     |------------------------------ */

    public function incomplete(): self
    {
        /* Make some fields empty to simulate incomplete profile */
        return $this->state(function () {
            return [
                'status' => CompanyStatus::INCOMPLETE->value,
                'email' => null,
                'phone' => null,
                'city' => null,
                'zipcode' => null,
            ];
        });
    }

    public function pending(): self
    {
        return $this->state(fn() => ['status' => CompanyStatus::PENDING->value]);
    }

    public function active(): self
    {
        return $this->state(fn() => ['status' => CompanyStatus::ACTIVE->value]);
    }

    public function inactive(): self
    {
        return $this->state(fn() => ['status' => CompanyStatus::INACTIVE->value]);
    }

    public function suspended(): self
    {
        return $this->state(fn() => ['status' => CompanyStatus::SUSPENDED->value]);
    }

    /**
     * Add Polish Data to Seeder.
     *
     * @return $this
     */
    public function withPolishData(): static
    {
        return $this->state(fn(array $attributes) => [
            'nip' => $this->faker->numerify('##########'),
            'regon' => $this->faker->numerify('#############'),
            'voivodeship' => $this->faker->randomElement([
                'dolnośląskie', 'kujawsko-pomorskie', 'lubelskie', 'lubuskie',
                'łódzkie', 'małopolskie', 'mazowieckie', 'opolskie',
                'podkarpackie', 'podlaskie', 'pomorskie', 'śląskie',
                'świętokrzyskie', 'warmińsko-mazurskie', 'wielkopolskie', 'zachodniopomorskie'
            ]),
            'zipcode' => $this->faker->postcode(),
        ]);
    }

    /* ------------------------------
     | Convenience: link to existing user
     |------------------------------ */

    public function forUser(User $user): self
    {
        /* Caller ensures $user->isCompany() */
        return $this->state(fn() => ['user_id' => $user->id]);
    }
}
