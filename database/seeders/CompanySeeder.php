<?php

namespace Database\Seeders;

use App\Enums\CompanyStatus;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /*
    |----------------------------------------------------------------------
    | Seeds companies for users with role COMPANY
    |----------------------------------------------------------------------
    | - Idempotent: creates a company only if user has none.
    | - Deterministic status cycle (no randomness).
    | - For ACTIVE/PENDING/INACTIVE we ensure basic completeness of profile.
    | - Licensing is handled by a separate model (not here).
    */

    public function run(): void
    {
        /* Eager-load to avoid N+1 */
        $companyUsers = User::query()
            ->with('company')
            ->where('role', UserRole::COMPANY->value)
            ->orderBy('id')
            ->get();

        if ($companyUsers->isEmpty()) {
            $this->command?->warn('No COMPANY users found. Run UserSeeder first.');
            return;
        }

        /* Deterministic cycle of statuses */
        $cycle = [
            CompanyStatus::ACTIVE,
            CompanyStatus::INACTIVE,
            CompanyStatus::PENDING,
            CompanyStatus::INCOMPLETE,
            CompanyStatus::SUSPENDED,
        ];

        $created = 0;
        foreach ($companyUsers as $i => $user) {
            /* Skip if already has a company (idempotent) */
            if ($user->company) {
                continue;
            }

            $status = $cycle[$i % count($cycle)];

            /* Choose factory state method from status */
            $stateMethod = match ($status) {
                CompanyStatus::ACTIVE => 'active',
                CompanyStatus::INACTIVE => 'inactive',
                CompanyStatus::PENDING => 'pending',
                CompanyStatus::INCOMPLETE => 'incomplete',
                CompanyStatus::SUSPENDED => 'suspended',
            };

            /* Minimal, readable company name */
            $name = 'Firma '.($user->last_name ?: ('User'.$user->id));

            /* Deterministic base contact data (avoid uniques conflicts) */
            $email = 'company.'.$user->id.'@example.test';
            $phone = '+48600'.str_pad((string) ($user->id % 1000), 3, '0', STR_PAD_LEFT).'000';

            /* For ACTIVE/PENDING/INACTIVE we ensure complete profile fields; INCOMPLETE uses factory state */
            $overrides = match ($status) {
                CompanyStatus::ACTIVE => [
                    'company_name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'city' => 'Warszawa',
                    'zipcode' => '00-001',
                ],
                CompanyStatus::PENDING => [
                    'company_name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'city' => 'Kraków',
                    'zipcode' => '30-001',
                ],
                CompanyStatus::INACTIVE => [
                    'company_name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'city' => 'Poznań',
                    'zipcode' => '60-001',
                ],
                CompanyStatus::SUSPENDED => [
                    'company_name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'city' => 'Łódź',
                    'zipcode' => '90-001',
                ],
                default => [], /* INCOMPLETE: leave fields to factory state */
            };

            /* Create via factory with proper status and linking to the user */
            Company::factory()
                ->forUser($user)
                ->withPolishData()
                ->{$stateMethod}()
                ->create($overrides);

            $created++;
        }
        $this->command?->info("CompanySeeder: created $created company profiles for COMPANY users without a company.");
    }
}
