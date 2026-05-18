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
        Schema::create('end_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('employee_id')->constrained()->cascadeOnDelete();
            $table->string('reason')->nullable();
            $table->string('type')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->json('data')->nullable() ; 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('end_contracts');
    }
};
