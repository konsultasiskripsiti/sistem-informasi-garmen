<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('suppliers', 'supplier_code')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->string('supplier_code')->nullable()->after('id');
            });
        }

        $suppliers = DB::table('suppliers')->orderBy('id')->get(['id']);

        foreach ($suppliers as $index => $supplier) {
            DB::table('suppliers')
                ->where('id', $supplier->id)
                ->where(function ($query) {
                    $query->whereNull('supplier_code')
                        ->orWhere('supplier_code', '');
                })
                ->update([
                    'supplier_code' => 'KDS'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                ]);
        }

        $driver = DB::getDriverName();

        $hasUniqueIndex = match ($driver) {
            'sqlite' => collect(DB::select("PRAGMA index_list('suppliers')"))
                ->contains(fn ($index) => $index->name === 'suppliers_supplier_code_unique'),
            default => collect(DB::select('SHOW INDEX FROM suppliers'))
                ->contains(fn ($index) => $index->Key_name === 'suppliers_supplier_code_unique'),
        };

        if (! $hasUniqueIndex) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->unique('supplier_code');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropUnique(['supplier_code']);
            $table->dropColumn('supplier_code');
        });
    }
};
