<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labels', function (Blueprint $table) {
            $table->id();
            $table->string('part_number');
            $table->unsignedInteger('quantity');
            $table->string('supplier_number');
            $table->string('vendor_batch_lot');
            $table->string('part_description')->nullable();
            $table->string('delivery_note')->nullable();
            $table->string('purchase_order')->nullable();
            $table->string('heat_lot')->nullable();
            $table->string('date_code')->nullable();
            $table->string('heat_code')->nullable();
            $table->string('revision_level')->nullable();
            $table->string('supplier_name')->nullable();
            $table->string('from_location')->nullable();
            $table->string('dana_facility')->nullable();
            $table->string('to_location')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('labels');
    }
};
