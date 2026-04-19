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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
             $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
             $table->string('status')->nullable();
           
             //contract details 
             $table->string('department');
            $table->string('job_title');
            $table->string('contract_type');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('base_salary', 10, 2);
            
            /*            
            $table->date('birth_date')->nullable();
            $table->string('nationality');
            $table->string('civility');
            $table->string('email')->unique();
            $table->string('phone', 9)->nullable();
            $table->integer('child')->default(0);
            $table->string('niu', 20)->nullable();
            $table->string('cnps_number')->nullable();
            $table->string('professional_category')->nullable();*/
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
