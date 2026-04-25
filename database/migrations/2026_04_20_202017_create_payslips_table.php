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
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->string('ref');
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->json('employee_data')->nullable();
            $table->json('company_data')->nullable();
            $table->string('status')->nullable();
            $table->json('elements_data')->nullable();
            $table->json('employee_contribution')->nullable();
            $table->json('employer_contribution')->nullable();
            $table->json('retenues_data')->nullable();
            $table->json('salaries_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
