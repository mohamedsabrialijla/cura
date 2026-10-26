<?php

namespace App\Services;

use App\Models\LandingCategoryStripItem;

class LandingCategoryStripService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAll(): array
    {
        return LandingCategoryStripItem::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (LandingCategoryStripItem $item) => $this->presentRow($item))
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $id): ?array
    {
        $item = $this->findModel($id);

        return $item ? $this->presentRow($item) : null;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function create(array $data): array
    {
        $sortOrder = LandingCategoryStripItem::query()->max('sort_order');

        $item = LandingCategoryStripItem::query()->create([
            'title_ar' => (string) ($data['title_ar'] ?? ''),
            'title_en' => (string) ($data['title_en'] ?? ''),
            'link' => (string) ($data['link'] ?? ''),
            'image' => $this->normalizeStoredFilename((string) ($data['image'] ?? '')),
            'sort_order' => is_numeric($sortOrder) ? ((int) $sortOrder + 1) : 0,
        ]);

        return $this->presentRow($item);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>|null
     */
    public function update(string $id, array $data): ?array
    {
        $item = $this->findModel($id);
        if ($item === null) {
            return null;
        }

        $item->fill([
            'title_ar' => (string) ($data['title_ar'] ?? $item->title_ar ?? ''),
            'title_en' => (string) ($data['title_en'] ?? $item->title_en ?? ''),
            'link' => (string) ($data['link'] ?? $item->link ?? ''),
            'image' => $this->normalizeStoredFilename(
                (string) ($data['image'] ?? $item->image ?? '')
            ),
        ]);
        $item->save();

        return $this->presentRow($item->fresh());
    }

    public function delete(string $id): bool
    {
        $item = $this->findModel($id);
        if ($item === null) {
            return false;
        }

        return (bool) $item->delete();
    }

    private function findModel(string $id): ?LandingCategoryStripItem
    {
        if ($id === '' || ! ctype_digit($id)) {
            return null;
        }

        return LandingCategoryStripItem::query()->find((int) $id);
    }

    /**
     * @return array<string, mixed>
     */
    private function presentRow(LandingCategoryStripItem $item): array
    {
        $image = (string) ($item->image ?? '');
        if ($image !== '' && ! str_starts_with($image, 'http')) {
            $image = url('uploads/settings/' . ltrim($image, '/'));
        }

        return [
            'id' => (string) $item->id,
            'title_ar' => (string) ($item->title_ar ?? ''),
            'title_en' => (string) ($item->title_en ?? ''),
            'link' => (string) ($item->link ?? ''),
            'image' => $image,
        ];
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
        if (! str_contains($incoming, '/') && ! str_starts_with($incoming, 'http')) {
            return $incoming;
        }

        return '';
    }
}
