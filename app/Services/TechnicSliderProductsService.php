<?php

namespace App\Services;

class TechnicSliderProductsService extends LandingSliderProductsService
{
    protected function storageFileName(): string
    {
        return 'landing_technic_slider_products.json';
    }
}
