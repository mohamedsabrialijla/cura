<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaqItem extends Model
{
    protected $fillable = [
        'category',
        'question_ar',
        'question_en',
        'answer_ar',
        'answer_en',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public $appends = ['category_label_ar', 'category_label_en'];

    /**
     * Fixed FAQ categories (same as public FAQ tabs).
     *
     * @return array<string, array{ar: string, en: string}>
     */
    public static function categories(): array
    {
        return [
            'support' => ['ar' => 'الدعم', 'en' => 'Support'],
            'i need help with my order' => ['ar' => 'أحتاج مساعدة بخصوص طلبي', 'en' => 'I need help with my order'],
            'payment options' => ['ar' => 'خيارات الدفع', 'en' => 'Payment options'],
            'shipping information' => ['ar' => 'معلومات الشحن', 'en' => 'Shipping information'],
            'returns' => ['ar' => 'الإرجاع', 'en' => 'Returns'],
            'glove-guide' => ['ar' => 'دليل القفازات', 'en' => 'Glove guide'],
            'goalkeeper glove models' => ['ar' => 'موديلات قفازات الحارس', 'en' => 'Goalkeeper glove models'],
            'goalkeeper glove cuts' => ['ar' => 'قصّات قفازات الحارس', 'en' => 'Goalkeeper glove cuts'],
            'glove-care' => ['ar' => 'العناية بالقفازات', 'en' => 'Glove care'],
            'pro tips on goalkeeper gloves palm foams' => ['ar' => 'نصائح محترفين حول إسفنج راحة القفاز', 'en' => 'Pro tips on goalkeeper gloves palm foams'],
            'newsletter' => ['ar' => 'النشرة البريدية', 'en' => 'Newsletter'],
            'contact us' => ['ar' => 'تواصل معنا', 'en' => 'Contact us'],
        ];
    }

    public static function categoryKeys(): array
    {
        return array_keys(self::categories());
    }

    public function getCategoryLabelArAttribute(): string
    {
        return self::categories()[$this->category]['ar'] ?? $this->category;
    }

    public function getCategoryLabelEnAttribute(): string
    {
        return self::categories()[$this->category]['en'] ?? $this->category;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
