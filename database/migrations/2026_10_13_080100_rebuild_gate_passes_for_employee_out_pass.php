<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The gate pass becomes the employee "Gate Pass / Out Work" form with a
 * Dept. Head -> HR -> Security approval flow. The old material gate pass
 * table is kept (renamed) so no data is lost.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('material_gate_passes')) {
            Schema::rename('gate_passes', 'material_gate_passes');
        }

        // Foreign key names are database-wide, so free the old name before the new table reuses it.
        try {
            Schema::table('material_gate_passes', fn (Blueprint $table) => $table->dropForeign('gate_passes_created_by_foreign'));
            Schema::table('material_gate_passes', fn (Blueprint $table) => $table->foreign('created_by')->references('id')->on('users')->nullOnDelete());
        } catch (\Throwable) {
            // already renamed by an earlier run
        }

        Schema::create('gate_passes', function (Blueprint $table) {
            $table->id();
            $table->string('pass_number')->unique();
            $table->date('pass_date');
            $table->string('employee_name');
            $table->string('father_name')->nullable();
            $table->string('department');
            $table->time('out_from');
            $table->time('out_to');
            $table->string('purpose');
            $table->string('status')->default('pending_hod');

            foreach (['hod', 'hr', 'security'] as $stage) {
                $table->foreignId("{$stage}_by")->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime("{$stage}_at")->nullable();
            }

            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('rejected_at')->nullable();
            $table->string('rejected_stage')->nullable();
            $table->string('reject_reason', 500)->nullable();

            $table->dateTime('out_time')->nullable();
            $table->dateTime('in_time')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gate_passes');
        Schema::table('material_gate_passes', fn (Blueprint $table) => $table->dropForeign('material_gate_passes_created_by_foreign'));
        Schema::rename('material_gate_passes', 'gate_passes');
        Schema::table('gate_passes', fn (Blueprint $table) => $table->foreign('created_by')->references('id')->on('users')->nullOnDelete());
    }
};
