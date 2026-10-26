<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingCategoryStripItem extends Model
{
    protected $fillable = [
        'title_ar',
        'title_en',
        'link',
        'image',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];
}
