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
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();

            $table->string('ref');
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // type d'absence
            $table->date('start_date'); // début
            $table->date('end_date'); // fin
            $table->string('days', 3); //nbres de jrs
            $table->string('status')->nullable(); // pending, approved, rejected
            $table->string('notes', 100)->nullable(); // notes
            $table->string('approved_by'); // qui a approuvé
             $table->date('last_leave')->nullable();
            $table->string('leaves_balance')->default(0);
            $table->string('leaves_majority')->default(0);
            $table->string('leaves_seniority')->default(0);
            $table->string('leaves_child')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
