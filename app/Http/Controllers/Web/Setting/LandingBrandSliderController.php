<?php

namespace App\Http\Controllers\Web\Setting;

use App\Http\Controllers\Controller;
use App\Services\LandingBrandSliderService;
use Illuminate\Http\Request;

class LandingBrandSliderController extends Controller
{
    protected LandingBrandSliderService $brandSliderService;

    public function __construct(LandingBrandSliderService $brandSliderService)
    {
        $this->brandSliderService = $brandSliderService;
    }

    public function getAll(Request $request)
    {
        return mainResponse(
            true,
            __('common.return'),
            $this->brandSliderService->getAll(),
            200,
            'items',
            ''
        );
    }

    public function createItem(Request $request)
    {
        $request->validate([
            'title_ar' => 'nullable|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:500',
            'image' => 'required|file|mimes:jpeg,png,jpg,webp,svg|max:4096',
        ]);

        $image = $this->storeImage($request->file('image'));

        $item = $this->brandSliderService->create([
            'title_ar' => (string) $request->input('title_ar', ''),
            'title_en' => (string) $request->input('title_en', ''),
            'link' => (string) $request->input('link', ''),
            'image' => $image,
        ]);

        return mainResponse(true, __('common.success'), $item, 200, 'item', '');
    }

    public function editItem(Request $request)
    {
        $request->validate([
            'id' => 'required|string|max:64',
            'title_ar' => 'nullable|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:500',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,webp,svg|max:4096',
        ]);

        $existing = $this->brandSliderService->find((string) $request->input('id'));
        if ($existing === null) {
            return mainResponse(false, __('common.error'), null, 404, 'item', '');
        }

        $image = $this->normalizeStoredFilename((string) ($existing['image'] ?? ''));
        if ($request->file('image') && $request->file('image')->isValid()) {
            $image = $this->storeImage($request->file('image'));
        }

        $item = $this->brandSliderService->update((string) $request->input('id'), [
            'title_ar' => (string) $request->input('title_ar', ''),
            'title_en' => (string) $request->input('title_en', ''),
            'link' => (string) $request->input('link', ''),
            'image' => $image,
        ]);

        return mainResponse(true, __('common.success'), $item, 200, 'item', '');
    }

    public function deleteItem(Request $request)
    {
        $request->validate([
            'id' => 'required|string|max:64',
        ]);

        $deleted = $this->brandSliderService->delete((string) $request->input('id'));
        if (! $deleted) {
            return mainResponse(false, __('common.error'), null, 404, 'item', '');
        }

        return mainResponse(true, __('common.success'), null, 200, 'item', '');
    }

    private function storeImage($file): string
    {
        $destinationPath = public_path('uploads/settings');
        $extension = strtolower($file->getClientOriginalExtension());
        $fileName = uniqid() . '_brand_slider.' . $extension;
        $file->move($destinationPath, $fileName);

        return $fileName;
    }

    private function normalizeStoredFilename(string $incoming): string
    {
        $incoming = trim($incoming);
        if ($incoming === '') {
            return '';
        }
        if (preg_match('~/uploads/settings/([^/?#]+)~i', $incoming, $m)) {
            return $m[1];
        }
        if (preg_match('~assets_website/[^?#]+~i', $incoming, $m)) {
            return $m[0];
        }
        if (preg_match('~(?:https?://[^/]+/)?assets_website/[^?#]+~i', $incoming, $m)) {
            return preg_replace('~^https?://[^/]+/~i', '', $m[0]);
        }
        if (! str_contains($incoming, '/') && ! str_starts_with($incoming, 'http')) {
            return $incoming;
        }

        return '';
    }
}
