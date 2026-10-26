<?php

namespace App\Http\Controllers\Web\Setting;

use App\Http\Controllers\Controller;
use App\Services\KeeperSportCatalogService;
use App\Services\TechnicSliderProductsService;
use Illuminate\Http\Request;

class TechnicalSliderController extends Controller
{
    protected $catalogService;
    protected $sliderService;

    public function __construct(KeeperSportCatalogService $catalogService, TechnicSliderProductsService $sliderService)
    {
        $this->catalogService = $catalogService;
        $this->sliderService = $sliderService;
    }

    public function getSelected(Request $request)
    {
        $payload = $this->sliderService->getPayload();

        return mainResponse(true, __('common.return'), $payload, 200, 'items', '');
    }

    public function getCategories(Request $request)
    {
        return mainResponse(
            true,
            __('common.return'),
            $this->catalogService->adminPickerCategories(),
            200,
            'items',
            ''
        );
    }

    public function getBrands(Request $request)
    {
        @set_time_limit(0);

        $validated = $request->validate([
            'locale' => ['nullable', 'in:ar,en'],
        ]);

        $locale = $validated['locale'] ?? 'en';
        $brands = $this->catalogService->brands($locale, 500);

        return mainResponse(true, __('common.return'), $brands, 200, 'items', '');
    }

    public function searchProducts(Request $request)
    {
        @set_time_limit(0);

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'brand' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:120'],
            'locale' => ['nullable', 'in:ar,en'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:120'],
        ]);

        $query = isset($validated['q']) ? trim($validated['q']) : '';
        $brand = isset($validated['brand']) ? trim($validated['brand']) : '';
        $category = isset($validated['category']) ? trim($validated['category']) : '';
        $locale = $validated['locale'] ?? 'en';
        $perPage = max(1, min(48, (int) ($validated['per_page'] ?? 24)));

        if ($query === '' && $brand === '' && $category === '') {
            return mainResponse(true, __('common.return'), [
                'items' => [],
                'facets' => [
                    'brands' => [],
                    'sizes' => [],
                    'colors' => [],
                    'attributes' => [],
                ],
                'pagination' => [
                    'page' => 1,
                    'per_page' => $perPage,
                    'total' => 0,
                    'last_page' => 1,
                ],
                'meta' => [
                    'requires_filter' => true,
                ],
            ], 200, 'items', '');
        }

        $products = $this->catalogService->products([
            'q' => $query,
            'brand' => $brand,
            'category' => $category,
            'page' => 1,
            'per_page' => $perPage,
            'sort' => 'newest_first',
            'scan_limit' => 300,
        ], $locale);

        return mainResponse(true, __('common.return'), $products, 200, 'items', '');
    }

    public function saveSelected(Request $request)
    {
        $validated = $request->validate([
            'products' => ['required', 'array', 'max:80'],
            'products.*.sku' => ['required', 'string'],
            'products.*.slug' => ['required', 'string'],
            'products.*.name' => ['required', 'string'],
            'products.*.image' => ['required', 'string'],
            'products.*.brand' => ['nullable', 'string'],
            'products.*.price_current' => ['required', 'numeric'],
            'products.*.price_old' => ['nullable', 'numeric'],
            'products.*.currency' => ['nullable', 'string'],
            'cta_link' => ['nullable', 'string', 'max:500'],
        ]);

        $saved = $this->sliderService->saveAll(
            $validated['products'],
            (string) ($validated['cta_link'] ?? '')
        );

        return mainResponse(true, __('common.success'), [
            'products' => $saved,
            'cta_link' => $this->sliderService->getCtaLink(),
        ], 200, 'items', '');
    }
}
