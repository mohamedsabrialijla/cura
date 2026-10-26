<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingTechnicSectionSetting extends Model
{
    protected $table = 'landing_technic_section_settings';

    protected $fillable = [
        'title_white_ar',
        'title_gold_ar',
        'description_ar',
        'title_white_en',
        'title_gold_en',
        'description_en',
    ];
}
