<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'invoice_number',
    'sale_date',
    'buyer_name',
    'buyer_phone',
    'buyer_address',
    'notes',
    'total_amount',
])]
class Sale extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'total_amount' => 'integer',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
