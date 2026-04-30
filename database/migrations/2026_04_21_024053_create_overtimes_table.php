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
        Schema::create('overtimes', function (Blueprint $table) {
            $table->id();
            $table->string('ref');
            $table->foreignUuid('employee_id')->constrained()->cascadeOnDelete();
            $table->integer('week');
            $table->string('day_type');
            $table->float('hours')->default(1);
            $table->float('hours_rate');
            $table->float('multiplier');
            $table->float('alloc')->default(0);
            $table->string('added_by');
            $table->string('notes', 100)->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtimes');
    }
};
