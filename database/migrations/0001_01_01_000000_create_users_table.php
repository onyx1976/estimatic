<?php

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /*
     * --------------------------------------------------------------------
     * Users table migration
     * --------------------------------------------------------------------
     * Key decisions:
     * - DB ENUMs for role/status (single source of truth; includes BLOCKED)
     * - Remove is_blocked flag (BLOCKED is a status)
     * - Rich auth & tracking fields; localization; JSON preferences
     * - Social auth IDs (unique); 2FA fields; soft deletes
     * - Full‑text index on name/email; standard indexes for role/status
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {

            $table->id();

            /* Role and Status with proper enum validation */
            $table->enum('role', array_column(UserRole::cases(), 'value'))
                ->default(UserRole::COMPANY->value)
                ->index('idx_users_role');

            $table->enum('status', array_column(UserStatus::cases(), 'value'))
                ->default(UserStatus::INCOMPLETE->value)
                ->index('idx_users_status');

            /* Personal Information */
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])
                ->nullable();

            /* Contact & Auth */
            $table->string('email')->unique();
            $table->string('phone', 30)->nullable()->index();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            /* Localization & Preferences */
            $table->string('language', 10)->default('pl');
            $table->string('locale', 20)->default('pl_PL');
            $table->string('timezone', 50)->default('Europe/Warsaw');
            $table->json('preferences')->nullable();

            /* Security & Activity Tracking */
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();  /* IPv4/IPv6 */
            $table->unsignedSmallInteger('login_failures')->default(0);

            /* Social OAuth IDs (unique when present) */
            $table->string('google_id')->unique()->nullable();
            $table->string('facebook_id')->unique()->nullable();
            $table->string('github_id')->unique()->nullable();

            /* Auditing (optional owners/admins) */
            $table->foreignId('created_by')->nullable()->index();
            $table->foreignId('updated_by')->nullable()->index();

            /* Maintenance */
            $table->softDeletes();
            $table->timestamps();

            /* Full‑text for search (requires MySQL 5.7+/InnoDB) */
            $table->fullText(['first_name', 'last_name', 'email'], 'ft_users_name_email');

            /* Helpful compound indexes */
            $table->index(['status', 'role'], 'idx_users_status_role');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
