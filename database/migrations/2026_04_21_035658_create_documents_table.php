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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
             $table->string('uuid');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->string('name');
            $table->string('notes', 100)->nullable();
            $table->string('path');
            $table->string('added_by');
            $table->string('access');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
