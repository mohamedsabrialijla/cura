<?php

namespace App\Http\Controllers\Web\Faqs;

use App\Http\Controllers\Controller;
use App\Models\FaqItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FaqController extends Controller
{
    public function categories()
    {
        $items = [];
        foreach (FaqItem::categories() as $slug => $labels) {
            $items[] = [
                'slug' => $slug,
                'title_ar' => $labels['ar'],
                'title_en' => $labels['en'],
            ];
        }

        return mainResponse(true, __('common.return'), $items, 200, 'items', '');
    }

    public function getAll(Request $request)
    {
        $query = FaqItem::query();

        if ($request->filled('category')) {
            $query->where('category', (string) $request->input('category'));
        }

        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where(function ($builder) use ($search) {
                $builder->where('question_ar', 'like', '%'.$search.'%')
                    ->orWhere('question_en', 'like', '%'.$search.'%')
                    ->orWhere('answer_ar', 'like', '%'.$search.'%')
                    ->orWhere('answer_en', 'like', '%'.$search.'%')
                    ->orWhere('category', 'like', '%'.$search.'%');
            });
        }

        $query->orderBy('category')->orderBy('sort_order')->orderBy('id');

        if ((int) $request->input('pagination', 1) === 1) {
            $items = $query->paginate(20);
        } else {
            $items = $query->get();
        }

        return mainResponse(true, __('common.return'), $items, 200, 'items', '');
    }

    public function getById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ID' => 'required|integer|exists:faq_items,id',
        ]);

        if ($validator->fails()) {
            return mainResponse(false, '', null, 203, 'items', $validator);
        }

        $item = FaqItem::query()->find($request->input('ID'));

        return mainResponse(true, __('common.return'), $item, 200, 'item', '');
    }

    public function create(Request $request)
    {
        $validator = $this->validator($request);
        if ($validator->fails()) {
            return mainResponse(false, '', null, 203, 'items', $validator);
        }

        $item = new FaqItem();
        $this->fillItem($item, $request);
        $item->save();

        return mainResponse(true, __('common.success'), $item, 200, 'items', '');
    }

    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Item_id' => 'required|integer|exists:faq_items,id',
        ]);

        if ($validator->fails()) {
            return mainResponse(false, '', null, 203, 'items', $validator);
        }

        $item = FaqItem::query()->findOrFail($request->input('Item_id'));
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
        $item = FaqItem::query()->find($id);
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

        FaqItem::query()->whereIn('id', $ids)->delete();

        return mainResponse(true, __('common.success'), '', 200, 'items', '');
    }

    private function validator(Request $request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request->all(), [
            'category' => ['required', 'string', Rule::in(FaqItem::categoryKeys())],
            'question_ar' => 'required|string|max:500',
            'question_en' => 'required|string|max:500',
            'answer_ar' => 'required|string',
            'answer_en' => 'required|string',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'status' => 'required|in:active,not_active',
        ]);
    }

    private function fillItem(FaqItem $item, Request $request): void
    {
        $item->category = (string) $request->input('category');
        $item->question_ar = trim((string) $request->input('question_ar'));
        $item->question_en = trim((string) $request->input('question_en'));
        $item->answer_ar = trim((string) $request->input('answer_ar'));
        $item->answer_en = trim((string) $request->input('answer_en'));
        $item->sort_order = $request->filled('sort_order') ? (int) $request->input('sort_order') : 0;
        $item->status = (string) $request->input('status', 'active');
    }
}
