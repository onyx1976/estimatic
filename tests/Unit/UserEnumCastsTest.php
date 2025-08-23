<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use ValueError;

/*
 |----------------------------------------------------------------------------
 | User enum casts - basic expectations
 |----------------------------------------------------------------------------
 | Verifies:
 | 1) role/status are returned as backed enums (type-safe)
 | 2) values persist in DB as strings
 | 3) assigning enum or raw string results in correct cast on read
 | 4) invalid values are rejected
 */
class UserEnumCastsTest extends TestCase
{
    use RefreshDatabase;


    public function test_role_and_status_are_cast_to_enums(): void
    {
        $user = User::factory()->create([
            'role'   => UserRole::OWNER->value,
            'status' => UserStatus::ACTIVE->value,
        ]);

        $this->assertInstanceOf(UserRole::class, $user->role);
        $this->assertInstanceOf(UserStatus::class, $user->status);
        $this->assertTrue($user->role === UserRole::OWNER);
        $this->assertTrue($user->status === UserStatus::ACTIVE);
    }

    public function test_persists_enum_values_as_strings_in_database(): void
    {
        $user = User::factory()->create([
            'role'   => UserRole::ADMIN->value,
            'status' => UserStatus::BLOCKED->value,
        ]);

        /* Raw DB values (no casting) */
        $rawRole   = DB::table('users')->where('id', $user->id)->value('role');
        $rawStatus = DB::table('users')->where('id', $user->id)->value('status');

        $allowedRoles    = array_map('strtoupper', UserRole::values());
        $allowedStatuses = array_map('strtoupper', UserStatus::values());

        $this->assertContains(strtoupper((string) $rawRole), $allowedRoles);
        $this->assertContains(strtoupper((string) $rawStatus), $allowedStatuses);

        $this->assertSame(0, strcasecmp(UserRole::ADMIN->value, (string) $rawRole));
        $this->assertSame(0, strcasecmp(UserStatus::BLOCKED->value, (string) $rawStatus));

        $fresh = User::find($user->id);
        $this->assertTrue($fresh->role === UserRole::ADMIN);
        $this->assertTrue($fresh->status === UserStatus::BLOCKED);
    }

    public function test_accepts_enum_instances_on_assignment(): void
    {
        $user = User::factory()->make();

        /* Assign enums directly */
        $user->role = UserRole::COMPANY;
        $user->status = UserStatus::INACTIVE;
        $user->email = 'enum.assign@example.test';
        $user->password = 'password';
        $user->first_name = 'Enum';
        $user->last_name  = 'Case';
        $user->save();

        $user->refresh();
        $this->assertTrue($user->role === UserRole::COMPANY);
        $this->assertTrue($user->status === UserStatus::INACTIVE);
    }

    public function test_rejects_invalid_enum_values(): void
    {
        $this->expectException(ValueError::class);

        $user = User::factory()->make([
            'email' => 'invalid.enum@example.test',
        ]);

        $user->role = 'SUPER_ADMIN'; /* not in UserRole */
        $user->status = 'PAUSED';    /* not in UserStatus */
        $user->password = 'password';
        $user->first_name = 'Bad';
        $user->last_name  = 'Value';
        $user->save();
    }
}
