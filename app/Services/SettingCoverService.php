<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class SettingCoverService
{
    private function filePath(): string
    {
        return storage_path('app/setting_cover.json');
    }

    /**
     * @return array<string, mixed>
     */
    public function get(): array
    {
        $path = $this->filePath();
        if (! File::exists($path)) {
            return $this->emptyPayload();
        }

        $decoded = json_decode((string) File::get($path), true);
        if (! is_array($decoded)) {
            return $this->emptyPayload();
        }

        return [
            'cover_link' => (string) ($decoded['cover_link'] ?? ''),
            'cover_image' => $this->resolvePublicImageUrl($decoded['cover_image'] ?? null),
            'cover_image_mobile' => $this->resolvePublicImageUrl($decoded['cover_image_mobile'] ?? null),
            'whiteout_link' => (string) ($decoded['whiteout_link'] ?? ''),
            'whiteout_image' => $this->resolvePublicImageUrl($decoded['whiteout_image'] ?? null),
            'whiteout_image_mobile' => $this->resolvePublicImageUrl($decoded['whiteout_image_mobile'] ?? null),
            'training_link' => (string) ($decoded['training_link'] ?? ''),
            'training_image' => $this->resolvePublicImageUrl($decoded['training_image'] ?? null),
            'training_image_mobile' => $this->resolvePublicImageUrl($decoded['training_image_mobile'] ?? null),
            'technic_link' => (string) ($decoded['technic_link'] ?? ''),
            'technic_image' => $this->resolvePublicImageUrl($decoded['technic_image'] ?? null),
            'technic_image_mobile' => $this->resolvePublicImageUrl($decoded['technic_image_mobile'] ?? null),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function save(array $payload): array
    {
        $current = $this->getRaw();
        $next = [
            'cover_link' => isset($payload['cover_link']) ? trim((string) $payload['cover_link']) : (string) ($current['cover_link'] ?? ''),
            'cover_image' => $payload['cover_image'] ?? ($current['cover_image'] ?? null),
            'cover_image_mobile' => $payload['cover_image_mobile'] ?? ($current['cover_image_mobile'] ?? null),
            'whiteout_link' => isset($payload['whiteout_link']) ? trim((string) $payload['whiteout_link']) : (string) ($current['whiteout_link'] ?? ''),
            'whiteout_image' => $payload['whiteout_image'] ?? ($current['whiteout_image'] ?? null),
            'whiteout_image_mobile' => $payload['whiteout_image_mobile'] ?? ($current['whiteout_image_mobile'] ?? null),
            'training_link' => isset($payload['training_link']) ? trim((string) $payload['training_link']) : (string) ($current['training_link'] ?? ''),
            'training_image' => $payload['training_image'] ?? ($current['training_image'] ?? null),
            'training_image_mobile' => $payload['training_image_mobile'] ?? ($current['training_image_mobile'] ?? null),
            'technic_link' => isset($payload['technic_link']) ? trim((string) $payload['technic_link']) : (string) ($current['technic_link'] ?? ''),
            'technic_image' => $payload['technic_image'] ?? ($current['technic_image'] ?? null),
            'technic_image_mobile' => $payload['technic_image_mobile'] ?? ($current['technic_image_mobile'] ?? null),
        ];

        File::ensureDirectoryExists(dirname($this->filePath()));
        File::put(
            $this->filePath(),
            json_encode($next, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        return $this->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function getRaw(): array
    {
        $path = $this->filePath();
        if (! File::exists($path)) {
            return [];
        }
        $decoded = json_decode((string) File::get($path), true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyPayload(): array
    {
        return [
            'cover_link' => '',
            'cover_image' => null,
            'cover_image_mobile' => null,
            'whiteout_link' => '',
            'whiteout_image' => null,
            'whiteout_image_mobile' => null,
            'training_link' => '',
            'training_image' => null,
            'training_image_mobile' => null,
            'technic_link' => '',
            'technic_image' => null,
            'technic_image_mobile' => null,
        ];
    }

    private function resolvePublicImageUrl(mixed $image): ?string
    {
        if (! is_string($image) || $image === '') {
            return null;
        }
        if (str_starts_with($image, 'http')) {
            return $image;
        }

        return url('uploads/settings/' . ltrim($image, '/'));
    }
}

