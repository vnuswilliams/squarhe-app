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
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->string('ref');
            $table->foreignUuid('employee_id')->constrained()->cascadeOnDelete();
            $table->string('base_salary');
            $table->string('gross_salary')->default(0);
            $table->string('intermediate_taxable_gross_salary')->default(0);
            $table->string('taxable_gross_salary')->default(0);
            $table->string('contributory_salary')->default(0);
            $table->string('average_salary')->default(0);
            $table->string('smic')->default(0)->default(0);
            $table->string('contributions')->default(0);
            $table->string('retenues')->default(0);
            $table->string('nap')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};
