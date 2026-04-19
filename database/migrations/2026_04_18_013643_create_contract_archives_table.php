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
        Schema::create('contract_archives', function (Blueprint $table) {
            $table->id();
              $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('motif')->nullable();
            $table->string('department');
            $table->string('job_title');
            $table->string('contract_type');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('base_salary', 10, 2);
            $table->string('category')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_archives');
    }
};
