<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeaderTickerSetting extends Model
{
    protected $fillable = [
        'is_enabled',
        'prefix_en',
        'prefix_ar',
        'cta_en',
        'cta_ar',
        'link_en',
        'link_ar',
        'bg_color',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];
}
