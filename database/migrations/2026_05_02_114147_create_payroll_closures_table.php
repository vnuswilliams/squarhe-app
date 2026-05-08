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
        Schema::create('payroll_closures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('ref'); // format m-Y
            $table->string('status'); // draft, closed, locked
            $table->boolean('send_payslips_by_email')->default(false);
            $table->date('scheduled_at')->nullable();
            $table->date('closed_at')->nullable();
            $table->string('closed_by')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('payroll_closure_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('status')->nullable();
            $table->string('department');
            $table->string('job_title');
            $table->string('contract_type');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('base_salary', 10, 2);
            $table->json('data')->nullable();
            $table->timestamps();
        });

        Schema::create('salary_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_closure_id')->constrained()->cascadeOnDelete();
            $table->string('ref');
            $table->foreignUuid('employee_snapshot_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->string('base_salary');
            $table->string('gross_salary')->default(0);
            $table->string('intermediate_taxable_gross_salary')->default(0);
            $table->string('taxable_gross_salary')->default(0);
            $table->string('contributory_salary')->default(0);
            $table->string('average_salary')->default(0);
            $table->string('smic')->default(0)->default(0);
            $table->string('retenues')->default(0);
            $table->string('contributions')->default(0);
            $table->string('nap')->default(0);
            $table->timestamps();
        });

        Schema::create('payslip_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_closure_id')->constrained()->cascadeOnDelete();

            $table->string('uuid');
            $table->string('ref');
            $table->foreignUuid('employee_snapshot_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('employee_id')->nullable()->constrained()->nullOnDelete();
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

        Schema::create('employee_contribution_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_closure_id')->constrained()->cascadeOnDelete();

            $table->string('ref');
            $table->foreignUuid('employee_snapshot_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->string('old_age_pension')->default(0);
            $table->string('irpp')->default(0);
            $table->string('cac')->default(0);
            $table->string('cfc')->default(0);
            $table->string('syndicat')->default(0);
            $table->string('rav')->default(0);
            $table->string('tdl')->default(0);
            $table->timestamps();
        });

        Schema::create('employer_contribution_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_closure_id')->constrained()->cascadeOnDelete();

            $table->string('ref');
            $table->foreignUuid('employee_snapshot_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->string('family_allowance')->default(0);
            $table->string('old_age_pension')->default(0);
            $table->string('accident')->default(0);
            $table->string('cfc')->default(0);
            $table->string('fne')->default(0);
            $table->timestamps();
        });

        Schema::create('remuneration_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_closure_id')->constrained()->cascadeOnDelete();

            $table->string('ref');
            $table->foreignUuid('employee_snapshot_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('type');
            $table->string('amount')->default(0);
            $table->string('periodicity');
            $table->string('impact');
            $table->string('added_by');
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('iran_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_closure_id')->constrained()->cascadeOnDelete();
            $table->string('ref');
            $table->foreignUuid('employee_snapshot_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('amount');
            $table->string('limit_fisc');
            $table->string('quote');
            $table->timestamps();
        });

        Schema::create('adv_nat_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_closure_id')->constrained()->cascadeOnDelete();
            $table->string('ref');
            $table->foreignUuid('employee_snapshot_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('amount');
            $table->string('limit_fisc');
            $table->string('excedent');
            $table->timestamps();
        });

        Schema::create('leave_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_closure_id')->constrained()->cascadeOnDelete();

            $table->string('ref');
            $table->foreignUuid('employee_snapshot_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // type d'absence
            $table->date('start_date'); // début
            $table->date('end_date'); // fin
            $table->string('days', 3); //nbres de jrs
            $table->string('status')->nullable(); // pending, approved, rejected
            $table->string('notes', 100)->nullable(); // notes
            $table->string('approved_by'); // qui a approuvé
            $table->date('approbation_date'); // qui a approuvé
             $table->date('last_leave')->nullable();
            $table->string('leaves_balance')->default(0);
            $table->string('leaves_majority')->default(0);
            $table->string('leaves_seniority')->default(0);
            $table->string('leaves_child')->default(0);
            $table->timestamps();
        });

        Schema::create('overtime_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_closure_id')->constrained()->cascadeOnDelete();

            $table->string('ref', 7);

            $table->foreignUuid('employee_snapshot_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('employee_id')->nullable()->constrained()->nullOnDelete();
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
        Schema::create('declaration_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_closure_id')->constrained()->cascadeOnDelete();

            $table->string('ref'); // format m-Y
            $table->string('status')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });

        Schema::create('payroll_book_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_closure_id')->constrained()->cascadeOnDelete();

            $table->string('ref');
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->json('data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_snapshots');
        Schema::dropIfExists('overtime_snapshots');
        Schema::dropIfExists('leave_snapshots');
        Schema::dropIfExists('adv_nat_snapshots');
        Schema::dropIfExists('iran_snapshots');
        Schema::dropIfExists('remuneration_snapshots');
        Schema::dropIfExists('employer_contribution_snapshots');
        Schema::dropIfExists('employee_contribution_snapshots');
        Schema::dropIfExists('payslip_snapshots');
        Schema::dropIfExists('salary_snapshots');
        Schema::dropIfExists('declaration_snapshots');
        Schema::dropIfExists('payroll_book_snapshots');
        Schema::dropIfExists('payroll_closures');
    }
};
