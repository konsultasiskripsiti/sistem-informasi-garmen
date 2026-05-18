<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'opname_date',
    'product_id',
    'person_in_charge_id',
    'system_quantity',
    'physical_quantity',
    'adjustment_quantity',
    'notes',
])]
class ProductStockOpname extends Model
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
            'system_quantity' => 'integer',
            'physical_quantity' => 'integer',
            'adjustment_quantity' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function personInCharge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'person_in_charge_id');
    }
}
