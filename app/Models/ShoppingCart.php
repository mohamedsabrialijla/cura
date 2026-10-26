<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShoppingCart extends Model
{
    protected $fillable = [
        'cart_token',
        'user_id',
        'items_count',
        'subtotal',
        'currency',
        'status',
        'last_activity_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'last_activity_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShoppingCartItem::class);
    }
}
