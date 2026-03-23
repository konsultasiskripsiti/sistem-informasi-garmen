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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('size', 10);
            $table->string('unit', 20)->default('Pcs');
            $table->unsignedBigInteger('unit_price');
            $table->timestamps();
        });

        Schema::create('product_raw_material', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raw_material_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->timestamps();

            $table->unique(['product_id', 'raw_material_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_raw_material');
        Schema::dropIfExists('products');
    }
};
