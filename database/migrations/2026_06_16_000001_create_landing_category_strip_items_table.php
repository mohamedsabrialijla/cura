<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_category_strip_items', function (Blueprint $table) {
            $table->id();
            $table->string('title_ar')->nullable();
            $table->string('title_en')->nullable();
            $table->string('link', 500)->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('sort_order');
        });

        $this->importFromLegacyJson();
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_category_strip_items');
    }

    private function importFromLegacyJson(): void
    {
        $path = storage_path('app/landing_category_strip.json');
        if (! File::exists($path)) {
            return;
        }

        $decoded = json_decode((string) File::get($path), true);
        if (! is_array($decoded)) {
            return;
        }

        $now = now();
        $rows = [];

        foreach ($decoded as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $image = trim((string) ($row['image'] ?? ''));
            if ($image !== '' && preg_match('~/uploads/settings/([^/?#]+)~i', $image, $m)) {
                $image = $m[1];
            } elseif ($image !== '' && (str_contains($image, '/') || str_starts_with($image, 'http'))) {
                $image = '';
            }

            $rows[] = [
                'title_ar' => (string) ($row['title_ar'] ?? ''),
                'title_en' => (string) ($row['title_en'] ?? ''),
                'link' => (string) ($row['link'] ?? ''),
                'image' => $image,
                'sort_order' => $index,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows !== []) {
            DB::table('landing_category_strip_items')->insert($rows);
        }
    }
};
