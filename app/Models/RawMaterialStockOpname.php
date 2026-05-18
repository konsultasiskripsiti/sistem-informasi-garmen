<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'opname_date',
    'raw_material_id',
    'person_in_charge_id',
    'system_quantity',
    'physical_quantity',
    'adjustment_quantity',
    'notes',
])]
class RawMaterialStockOpname extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'opname_date' => 'datetime',
            'system_quantity' => 'decimal:2',
            'physical_quantity' => 'decimal:2',
            'adjustment_quantity' => 'decimal:2',
        ];
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class);
    }

    public function personInCharge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'person_in_charge_id');
    }
}
