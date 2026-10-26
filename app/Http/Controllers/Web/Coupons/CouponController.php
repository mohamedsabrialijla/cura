<?php

namespace App\Http\Controllers\Web\Coupons;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function getAll(Request $request)
    {
        $query = Coupon::query();

        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where(function ($builder) use ($search) {
                $builder->where('code', 'like', '%' . $search . '%')
                    ->orWhere('type', 'like', '%' . $search . '%');
            });
        }

        if ((int) $request->input('pagination', 1) === 1) {
            $items = $query->orderByDesc('id')->paginate(15);
        } else {
            $items = $query->orderByDesc('id')->get();
        }

        return mainResponse(true, __('common.return'), $items, 200, 'items', '');
    }

    public function getById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ID' => 'required|integer|exists:coupons,id',
        ]);

        if ($validator->fails()) {
            return mainResponse(false, '', null, 203, 'items', $validator);
        }

        $item = Coupon::query()->find($request->input('ID'));

        return mainResponse(true, __('common.return'), $item, 200, 'item', '');
    }

    public function create(Request $request)
    {
        $validator = $this->validator($request);

        if ($validator->fails()) {
            return mainResponse(false, '', null, 203, 'items', $validator);
        }

        $item = new Coupon();
        $this->fillCoupon($item, $request);
        $item->used_count = 0;
        $item->save();

        return mainResponse(true, __('common.success'), $item, 200, 'items', '');
    }

    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Item_id' => 'required|integer|exists:coupons,id',
        ]);

        if ($validator->fails()) {
            return mainResponse(false, '', null, 203, 'items', $validator);
        }

        $item = Coupon::query()->findOrFail($request->input('Item_id'));
        $validator = $this->validator($request, $item->id);

        if ($validator->fails()) {
            return mainResponse(false, '', null, 203, 'items', $validator);
        }

        $this->fillCoupon($item, $request);
        $item->save();

        return mainResponse(true, __('common.success'), $item, 200, 'items', '');
    }

    public function delete($id)
    {
        $item = Coupon::query()->find($id);
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

        Coupon::query()->whereIn('id', $ids)->delete();

        return mainResponse(true, __('common.success'), '', 200, 'items', '');
    }

    private function validator(Request $request, ?int $ignoreId = null): \Illuminate\Contracts\Validation\Validator
    {
        $rules = [
            'code' => [
                'required',
                'string',
                'max:64',
                Rule::unique('coupons', 'code')->ignore($ignoreId),
            ],
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_order' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'status' => 'required|in:active,not_active',
        ];

        if ($request->input('type') === 'percentage') {
            $rules['value'] = 'required|numeric|min:0|max:100';
        }

        return Validator::make($request->all(), $rules);
    }

    private function fillCoupon(Coupon $item, Request $request): void
    {
        $item->code = strtoupper(trim((string) $request->input('code')));
        $item->type = (string) $request->input('type');
        $item->value = $request->input('value');
        $item->min_order = $request->filled('min_order') ? $request->input('min_order') : null;
        $item->max_uses = $request->filled('max_uses') ? (int) $request->input('max_uses') : null;
        $item->expires_at = $request->filled('expires_at') ? $request->input('expires_at') : null;
        $item->status = (string) $request->input('status', 'active');
    }
}
