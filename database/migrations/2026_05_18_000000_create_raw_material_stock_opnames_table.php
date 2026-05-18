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
        Schema::create('raw_material_stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->date('opname_date');
            $table->foreignId('raw_material_id')->constrained()->cascadeOnDelete();
            $table->foreignId('person_in_charge_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('system_quantity', 12, 2);
            $table->decimal('physical_quantity', 12, 2);
            $table->decimal('adjustment_quantity', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raw_material_stock_opnames');
    }
};
