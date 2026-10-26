<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    /** طرق دفع مخفية من المتجر (ديسكتوب + جوال). */
    public const HIDDEN_STOREFRONT_CODES = [
        'tabby',
        'tamara',
    ];

    protected $fillable = [
        'code',
        'title_ar',
        'title_en',
        'description_ar',
        'description_en',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeVisibleOnStorefront($query)
    {
        return $query
            ->whereNotIn('code', self::HIDDEN_STOREFRONT_CODES)
            ->whereRaw("LOWER(COALESCE(title_en,'')) NOT LIKE '%tabby%'")
            ->whereRaw("LOWER(COALESCE(title_en,'')) NOT LIKE '%tamara%'")
            ->whereRaw("COALESCE(title_ar,'') NOT LIKE '%تابي%'")
            ->whereRaw("COALESCE(title_ar,'') NOT LIKE '%تمارا%'");
    }
}
