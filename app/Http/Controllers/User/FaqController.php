<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\FaqItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';

        if (! Schema::hasTable('faq_items')) {
            return response()->json([
                'status' => true,
                'from_db' => false,
                'sections' => [],
            ]);
        }

        $rows = FaqItem::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $grouped = [];
        foreach ($rows as $row) {
            $slug = $row->category;
            if (! isset($grouped[$slug])) {
                $labels = FaqItem::categories()[$slug] ?? null;
                $grouped[$slug] = [
                    'slug' => $slug,
                    'title' => $locale === 'ar'
                        ? ($labels['ar'] ?? $row->category_label_ar)
                        : ($labels['en'] ?? $row->category_label_en),
                    'items' => [],
                ];
            }

            $grouped[$slug]['items'][] = [
                'q' => $locale === 'ar' ? $row->question_ar : $row->question_en,
                'a' => $locale === 'ar' ? $row->answer_ar : $row->answer_en,
            ];
        }

        // Preserve category order from definition
        $sections = [];
        foreach (FaqItem::categoryKeys() as $slug) {
            if (! empty($grouped[$slug]['items'])) {
                $sections[] = $grouped[$slug];
            }
        }

        return response()->json([
            'status' => true,
            'from_db' => $rows->isNotEmpty(),
            'sections' => $sections,
        ]);
    }
}
