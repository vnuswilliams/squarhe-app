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
        Schema::create('employee_contributions', function (Blueprint $table) {
            $table->id();
            $table->string('ref');
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('old_age_pension')->default(0);
            $table->string('irpp')->default(0);
            $table->string('cac')->default(0);
            $table->string('cfc')->default(0);
            $table->string('syndicat')->default(0);
            $table->string('rav')->default(0);
            $table->string('tdl')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_contributions');
    }
};
