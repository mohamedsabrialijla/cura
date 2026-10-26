<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoppingCartItem extends Model
{
    protected $fillable = [
        'shopping_cart_id',
        'product_key',
        'sku',
        'title',
        'subtitle',
        'price',
        'qty',
        'image',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'qty' => 'integer',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(ShoppingCart::class, 'shopping_cart_id');
    }
}
