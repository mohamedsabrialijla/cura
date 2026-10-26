<?php

namespace App\Http\Controllers\Web\Shipping;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShippingMethodController extends Controller
{
    public function getAll(Request $request)
    {
        $query = ShippingMethod::query();

        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where(function ($builder) use ($search) {
                $builder->where('title_ar', 'like', '%' . $search . '%')
                    ->orWhere('title_en', 'like', '%' . $search . '%');
            });
        }

        if ((int) $request->input('pagination', 1) === 1) {
            $items = $query->orderBy('sort_order')->orderBy('id')->paginate(15);
        } else {
            $items = $query->orderBy('sort_order')->orderBy('id')->get();
        }

        return mainResponse(true, __('common.return'), $items, 200, 'items', '');
    }

    public function getById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ID' => 'required|integer|exists:shipping_methods,id',
        ]);

        if ($validator->fails()) {
            return mainResponse(false, '', null, 203, 'items', $validator);
        }

        $item = ShippingMethod::query()->find($request->input('ID'));

        return mainResponse(true, __('common.return'), $item, 200, 'item', '');
    }

    public function create(Request $request)
    {
        $validator = $this->validator($request);

        if ($validator->fails()) {
            return mainResponse(false, '', null, 203, 'items', $validator);
        }

        $item = new ShippingMethod();
        $this->fillItem($item, $request);
        $item->save();

        return mainResponse(true, __('common.success'), $item, 200, 'items', '');
    }

    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Item_id' => 'required|integer|exists:shipping_methods,id',
        ]);

        if ($validator->fails()) {
            return mainResponse(false, '', null, 203, 'items', $validator);
        }

        $item = ShippingMethod::query()->findOrFail($request->input('Item_id'));
        $validator = $this->validator($request);

        if ($validator->fails()) {
            return mainResponse(false, '', null, 203, 'items', $validator);
        }

        $this->fillItem($item, $request);
        $item->save();

        return mainResponse(true, __('common.success'), $item, 200, 'items', '');
    }

    public function delete($id)
    {
        $item = ShippingMethod::query()->find($id);
        if (! $item) {
            return mainResponse(false, __('common.error'), null, 404, 'items', '');
        }

        $item->delete();

        return mainResponse(true, __('common.success'), '', 200, 'items', '');
    }

    public function deleteAll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'selectedItems' => 'required|string',
        ]);

        if ($validator->fails()) {
            return mainResponse(false, '', null, 203, 'items', $validator);
        }

        $ids = array_filter(array_map('intval', explode(',', (string) $request->input('selectedItems'))));
        if ($ids === []) {
            return mainResponse(false, __('common.error'), null, 203, 'items', '');
        }

        ShippingMethod::query()->whereIn('id', $ids)->delete();

        return mainResponse(true, __('common.success'), '', 200, 'items', '');
    }

    private function validator(Request $request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request->all(), [
            'title_ar' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'delivery_days' => 'required|string|min:1|max:120',
            'price' => 'required|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'status' => 'required|in:active,not_active',
        ]);
    }

    private function fillItem(ShippingMethod $item, Request $request): void
    {
        $item->title_ar = trim((string) $request->input('title_ar'));
        $item->title_en = trim((string) $request->input('title_en'));
        $item->delivery_days = trim((string) $request->input('delivery_days'));
        $item->price = $request->input('price');
        $item->sort_order = $request->filled('sort_order') ? (int) $request->input('sort_order') : 0;
        $item->status = (string) $request->input('status', 'active');
    }
}
