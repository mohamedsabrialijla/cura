<?php

namespace App\Http\Controllers\Web\PaymentMethods;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PaymentMethodController extends Controller
{
    public function getAll(Request $request)
    {
        $query = PaymentMethod::query();

        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where(function ($builder) use ($search) {
                $builder->where('title_ar', 'like', '%'.$search.'%')
                    ->orWhere('title_en', 'like', '%'.$search.'%')
                    ->orWhere('code', 'like', '%'.$search.'%');
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
            'ID' => 'required|integer|exists:payment_methods,id',
        ]);

        if ($validator->fails()) {
            return mainResponse(false, '', null, 203, 'items', $validator);
        }

        $item = PaymentMethod::query()->find($request->input('ID'));

        return mainResponse(true, __('common.return'), $item, 200, 'item', '');
    }

    public function create(Request $request)
    {
        $validator = $this->validator($request);

        if ($validator->fails()) {
            return mainResponse(false, '', null, 203, 'items', $validator);
        }

        $item = new PaymentMethod();
        $this->fillItem($item, $request);
        $item->save();

        return mainResponse(true, __('common.success'), $item, 200, 'items', '');
    }

    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Item_id' => 'required|integer|exists:payment_methods,id',
        ]);

        if ($validator->fails()) {
            return mainResponse(false, '', null, 203, 'items', $validator);
        }

        $item = PaymentMethod::query()->findOrFail($request->input('Item_id'));
        $validator = $this->validator($request, $item->id);

        if ($validator->fails()) {
            return mainResponse(false, '', null, 203, 'items', $validator);
        }

        $this->fillItem($item, $request);
        $item->save();

        return mainResponse(true, __('common.success'), $item, 200, 'items', '');
    }

    public function delete($id)
    {
        $item = PaymentMethod::query()->find($id);
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

        PaymentMethod::query()->whereIn('id', $ids)->delete();

        return mainResponse(true, __('common.success'), '', 200, 'items', '');
    }

    private function validator(Request $request, ?int $ignoreId = null): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request->all(), [
            'code' => [
                'required',
                'string',
                'max:64',
                'regex:/^[a-z0-9_\-]+$/',
                Rule::unique('payment_methods', 'code')->ignore($ignoreId),
            ],
            'title_ar' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string|max:500',
            'description_en' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'status' => 'required|in:active,not_active',
        ]);
    }

    private function fillItem(PaymentMethod $item, Request $request): void
    {
        $item->code = strtolower(trim((string) $request->input('code')));
        $item->title_ar = trim((string) $request->input('title_ar'));
        $item->title_en = trim((string) $request->input('title_en'));
        $item->description_ar = trim((string) $request->input('description_ar', '')) ?: null;
        $item->description_en = trim((string) $request->input('description_en', '')) ?: null;
        $item->sort_order = $request->filled('sort_order') ? (int) $request->input('sort_order') : 0;
        $item->status = (string) $request->input('status', 'active');
    }
}
