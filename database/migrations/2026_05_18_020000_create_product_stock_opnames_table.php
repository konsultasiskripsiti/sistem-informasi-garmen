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
        Schema::create('product_stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->dateTime('opname_date');
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('person_in_charge_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('system_quantity');
            $table->unsignedBigInteger('physical_quantity');
            $table->bigInteger('adjustment_quantity');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stock_opnames');
    }
};
