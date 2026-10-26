<?php

namespace App\Services;

use App\Models\HeaderTickerSetting;

class HeaderTickerService
{
    /**
     * @return array<string, mixed>
     */
    public function get(): array
    {
        $row = HeaderTickerSetting::query()->first();
        if ($row === null) {
            return $this->defaults();
        }

        $defaults = $this->defaults();

        return [
            'is_enabled' => (bool) ($row->is_enabled ?? $defaults['is_enabled']),
            'prefix_en' => (string) ($row->prefix_en ?: $defaults['prefix_en']),
            'prefix_ar' => (string) ($row->prefix_ar ?: $defaults['prefix_ar']),
            'cta_en' => (string) ($row->cta_en ?: $defaults['cta_en']),
            'cta_ar' => (string) ($row->cta_ar ?: $defaults['cta_ar']),
            'link_en' => (string) ($row->link_en ?: $defaults['link_en']),
            'link_ar' => (string) ($row->link_ar ?: $defaults['link_ar']),
            'bg_color' => $this->sanitizeColor($row->bg_color ?? null, $defaults['bg_color']),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function save(array $data): array
    {
        $row = HeaderTickerSetting::query()->firstOrNew(['id' => 1]);
        $row->is_enabled = filter_var($data['ticker_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $row->prefix_en = trim((string) ($data['ticker_prefix_en'] ?? ''));
        $row->prefix_ar = trim((string) ($data['ticker_prefix_ar'] ?? ''));
        $row->cta_en = trim((string) ($data['ticker_cta_en'] ?? ''));
        $row->cta_ar = trim((string) ($data['ticker_cta_ar'] ?? ''));
        $row->link_en = trim((string) ($data['ticker_link_en'] ?? ''));
        $row->link_ar = trim((string) ($data['ticker_link_ar'] ?? ''));
        $row->bg_color = $this->sanitizeColor($data['ticker_bg_color'] ?? null, '#000000');
        $row->save();

        return $this->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function defaults(): array
    {
        return [
            'is_enabled' => true,
            'prefix_en' => 'KEEPERsport Saudi Arabia | Goalkeeper specialists',
            'prefix_ar' => 'KEEPERsport السعودية | متخصصون في حراس المرمى',
            'cta_en' => '',
            'cta_ar' => '',
            'link_en' => '',
            'link_ar' => '',
            'bg_color' => '#000000',
        ];
    }

    private function sanitizeColor(mixed $value, string $fallback = '#000000'): string
    {
        $raw = trim((string) $value);
        if (preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $raw)) {
            return strtolower($raw);
        }

        return $fallback;
    }
}
