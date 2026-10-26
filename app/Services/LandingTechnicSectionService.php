<?php

namespace App\Services;

use App\Models\LandingTechnicSectionSetting;

class LandingTechnicSectionService
{
    /**
     * @return array<string, string>
     */
    public function get(): array
    {
        $row = LandingTechnicSectionSetting::query()->first();
        if ($row === null) {
            return $this->defaults();
        }

        $defaults = $this->defaults();

        return [
            'title_white_ar' => (string) ($row->title_white_ar ?: $defaults['title_white_ar']),
            'title_gold_ar' => (string) ($row->title_gold_ar ?: $defaults['title_gold_ar']),
            'description_ar' => (string) ($row->description_ar ?: $defaults['description_ar']),
            'title_white_en' => (string) ($row->title_white_en ?: $defaults['title_white_en']),
            'title_gold_en' => (string) ($row->title_gold_en ?: $defaults['title_gold_en']),
            'description_en' => (string) ($row->description_en ?: $defaults['description_en']),
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    public function save(array $data): array
    {
        $row = LandingTechnicSectionSetting::query()->firstOrNew(['id' => 1]);
        $row->title_white_ar = trim((string) ($data['title_white_ar'] ?? ''));
        $row->title_gold_ar = trim((string) ($data['title_gold_ar'] ?? ''));
        $row->description_ar = trim((string) ($data['description_ar'] ?? ''));
        $row->title_white_en = trim((string) ($data['title_white_en'] ?? ''));
        $row->title_gold_en = trim((string) ($data['title_gold_en'] ?? ''));
        $row->description_en = trim((string) ($data['description_en'] ?? ''));
        $row->save();

        return $this->get();
    }

    /**
     * @return array<string, string>
     */
    private function defaults(): array
    {
        return [
            'title_white_ar' => 'ملابس',
            'title_gold_ar' => 'تقنية',
            'description_ar' => 'مواد عالية الأداء، وتقنيات مدروسة بدقة، وأنظمة حماية متطورة: كل مجموعة مصممة لتمنحك تحكمًا وراحة وأقصى أمان في أي ظروف لعب.',
            'title_white_en' => 'TECHNICAL',
            'title_gold_en' => 'APPAREL',
            'description_en' => 'High-performance materials, carefully engineered technologies and advanced protection systems: every collection is designed to give you control, comfort and maximum safety in any playing conditions.',
        ];
    }
}
