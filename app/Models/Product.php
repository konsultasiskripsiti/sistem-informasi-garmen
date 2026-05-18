<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'size',
    'unit',
    'unit_price',
    'stock_quantity',
])]
class Product extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'integer',
            'stock_quantity' => 'integer',
        ];
    }

    public function rawMaterials(): BelongsToMany
    {
        return $this->belongsToMany(RawMaterial::class, 'product_raw_material')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function productions(): HasMany
    {
        return $this->hasMany(Production::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockOpnames(): HasMany
    {
        return $this->hasMany(ProductStockOpname::class);
    }
}
