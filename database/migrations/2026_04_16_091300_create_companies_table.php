<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->string('adresse');
            $table->string('city');
            $table->string('nui')->unique()->nullable();
            $table->string('cnps')->unique()->nullable();
            $table->string('rccm')->unique()->nullable();
            $table->uuid('join_code')->unique();
            $table->json('data');
            $table->softDeletes(); // This will add the deleted_at column for soft deletes
            $table->timestamps();
        });

        //Add the relation with the user 
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });

        Schema::dropIfExists('companies');
    }
};
