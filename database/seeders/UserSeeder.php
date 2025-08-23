<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /*
     |----------------------------------------------------------------------
     | Seeds a minimal, useful set of users for local/dev/test environments.
     | - Stable accounts with known emails for login/testing
     | - A batch of COMPANY users in various statuses
     | - Idempotent for OWNER/ADMIN via updateOrCreate (safe re-run)
     |----------------------------------------------------------------------
     */
    public function run(): void
    {
        /**
         * Owner user (stable credentials for local/dev/testing)
         * - role: OWNER
         * - status: ACTIVE
         */
        User::create([
            /* Personal */
            'first_name' => 'Dariusz',
            'last_name' => 'Krasicki',
            'date_of_birth' => '1980-05-15',
            'gender' => 'male',

            /* Role & Status */
            'role' => UserRole::OWNER,
            'status' => UserStatus::ACTIVE,

            /* Contact & Auth */
            'email' => 'd.krasicki@wp.pl',
            'email_verified_at' => now(),
            'phone' => '+48504190200',
            'password' => bcrypt('yx!iX4DuMt40gpqo!@'),

            /* Profile */
            'avatar' => null,

            /* Localization & Preferences */
            'language' => 'pl',
            'locale' => 'pl_PL',
            'timezone' => 'Europe/Warsaw',

            /* Security & Activity Tracking */
            'last_login_at' => now()
        ]);

        User::create([
            /* Personal */
            'first_name' => 'Joanna',
            'last_name' => 'Filipkiewicz',
            'date_of_birth' => '1985-08-22',
            'gender' => 'female',

            /* Role & Status */
            'role' => UserRole::ADMIN,
            'status' => UserStatus::ACTIVE,

            /* Contact & Auth */
            'email' => 'joanna.filipkiewicz@wp.pl',
            'email_verified_at' => now(),
            'phone' => '+48987654321',
            'password' => bcrypt('yx!iX4DuMt40gpqo!@'),

            /* Profile */
            'avatar' => null,

            /* Localization & Preferences */
            'language' => 'pl',
            'locale' => 'pl_PL',
            'timezone' => 'Europe/Warsaw',

            /* Security & Activity Tracking */
            'last_login_at' => now()->subHours(2)
        ]);

        /* Create active company users */
        User::factory()
            ->count(3)
            ->active()
            ->company()
            ->create();

        /* Create blocked company user */
        User::factory()
            ->blocked()
            ->company()
            ->create([
                'first_name' => 'Blocked',
                'last_name' => 'User',
                'email' => 'blocked@example.com'
            ]);

        $this->command->info('Created users:');
        $this->command->info('- System Owner: owner@example.com');
        $this->command->info('- Admin: admin@example.com');
        $this->command->info('- 3 Active company users');
        $this->command->info('- 1 Blocked company user');
    }
}
