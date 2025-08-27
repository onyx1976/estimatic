<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            /* Unique key name for setting */
            $table->string('key')->unique();

            /* JSON value to hold any scalar/array/object */
            $table->json('value')->nullable();

            /* Optional type hint for debugging/ops (string,int,bool,array,json) */
            $table->string('type', 20)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
