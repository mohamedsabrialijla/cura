<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAddress extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'email',
        'mobile',
        'country_code',
        'country_name',
        'city',
        'district',
        'street',
        'building',
        'apartment',
        'postal_code',
        'notes',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isSaudi(): bool
    {
        $code = strtoupper(trim((string) $this->country_code));

        return $code === 'SA' || $code === 'SAU';
    }
}
