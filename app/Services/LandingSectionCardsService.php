<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class LandingSectionCardsService
{
    private function filePath(): string
    {
        return storage_path('app/landing_section_cards.json');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAll(): array
    {
        $path = $this->filePath();
        if (! File::exists($path)) {
            return $this->defaults();
        }

        $decoded = json_decode((string) File::get($path), true);
        if (! is_array($decoded)) {
            return $this->defaults();
        }

        $defaults = $this->defaults();
        $out = [];
        foreach ($defaults as $index => $def) {
            $row = is_array($decoded[$index] ?? null) ? $decoded[$index] : [];
            $image = (string) ($row['image'] ?? $def['image']);
            if ($image !== '' && ! str_starts_with($image, 'http')) {
                $image = url('uploads/settings/' . ltrim($image, '/'));
            }
            $out[] = [
                'id' => $def['id'],
                'title_ar' => (string) ($row['title_ar'] ?? $def['title_ar']),
                'title_en' => (string) ($row['title_en'] ?? $def['title_en']),
                'link' => (string) ($row['link'] ?? $def['link']),
                'image' => $image,
            ];
        }

        return $out;
    }

    /**
     * @param array<int, array<string, mixed>> $cards
     * @return array<int, array<string, mixed>>
     */
    public function saveAll(array $cards): array
    {
        File::ensureDirectoryExists(dirname($this->filePath()));
        File::put(
            $this->filePath(),
            json_encode($cards, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        return $this->getAll();
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function defaults(): array
    {
        return [
            ['id' => 'deal_1', 'title_ar' => 'عرض الأسبوع', 'title_en' => 'Deal of the Week', 'link' => '', 'image' => ''],
            ['id' => 'deal_2', 'title_ar' => 'تخفيضات كبيرة', 'title_en' => 'Mega Sale', 'link' => '', 'image' => ''],
            ['id' => 'news_1', 'title_ar' => 'أحدث الأخبار', 'title_en' => 'Latest News', 'link' => '', 'image' => ''],
            ['id' => 'news_2', 'title_ar' => 'أحدث الأخبار', 'title_en' => 'Latest News', 'link' => '', 'image' => ''],
        ];
    }
}

