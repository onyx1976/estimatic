<?php

use App\Enums\CompanyStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /*
     |----------------------------------------------------------------------
     | Companies table
     |----------------------------------------------------------------------
     | One-to-one with User (role=COMPANY enforced w warstwie aplikacji).
     | - status: CompanyStatus enum (INCOMPLETE, PENDING, ACTIVE, INACTIVE, SUSPENDED)
     | - identifiers: NIP, REGON (strings, unique when present)
     | - contact: company email/phones
     | - address: normalized for easy filtering
     | - license: handled in a separate table/model (NOT here)
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            /* One-to-one with User; single company per user */
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->unique('user_id', 'uniq_companies_user_id'); /* separate unique index */

            /* Company lifecycle status (enum) */
            $table->enum('status', CompanyStatus::values())
                ->default(CompanyStatus::INCOMPLETE->value)
                ->index('idx_companies_status');

            /* Basic identity */
            $table->string('company_name', 200)->index('idx_companies_name'); /* quick search/sort */
            $table->string('brand_name', 200)->nullable(); /* optional public brand */
            $table->string('email', 191)->nullable()->index();                /* not globally unique */
            $table->string('phone', 30)->nullable()->index();
            $table->string('phone_alt', 30)->nullable();

            /* Legal identifiers (PL) */
            $table->string('nip', 20)->nullable()->unique();   /* normalized digits; allow multiple NULLs */
            $table->string('regon', 20)->nullable()->unique(); /* optional; unique when present */

            /* Address (normalized; all optional to allow INCOMPLETE) */
            $table->string('street', 150)->nullable();
            $table->string('building_no', 20)->nullable();
            $table->string('apartment_no', 20)->nullable();
            $table->string('city', 120)->nullable()->index();
            $table->string('zipcode', 12)->nullable()->index(); /* normalized with dash: 00-000 */
            $table->string('voivodeship', 60)->nullable(); /* optional PL region */
            $table->string('country_code', 2)->default('PL'); /* ISO-3166-1 alpha-2 */

            /* Web presence */
            $table->string('website', 255)->nullable();
            $table->string('logo_path', 255)->nullable(); /* storage path if used */

            /* Preferences / extras */
            $table->json('meta')->nullable(); /* lightweight key-value (e.g., tags, sizes) */

            $table->softDeletes();
            $table->timestamps();

            /* Helpful compound index for common filters */
            $table->index(['status', 'city'], 'idx_companies_status_city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
