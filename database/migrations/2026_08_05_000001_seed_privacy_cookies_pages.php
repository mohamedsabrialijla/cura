<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pages') || ! Schema::hasColumn('pages', 'page_key')) {
            return;
        }

        $now = now();

        // Ensure privacy exists (may already from older migration)
        $privacyId = DB::table('pages')->where('page_key', 'privacy')->value('id');
        if ($privacyId === null) {
            $privacyId = DB::table('pages')->insertGetId([
                'page_key' => 'privacy',
                'image' => null,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]);
        }

        $this->upsertTranslation($privacyId, 'en', [
            'name' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'key_words' => 'privacy',
            'description' => '<p>Add your privacy policy content from the dashboard.</p>',
        ]);
        $this->upsertTranslation($privacyId, 'ar', [
            'name' => 'سياسة الخصوصية',
            'slug' => 'سياسة-الخصوصية',
            'key_words' => 'خصوصية',
            'description' => '<p>أضف نص سياسة الخصوصية من لوحة التحكم.</p>',
        ]);

        $cookiesId = DB::table('pages')->where('page_key', 'cookies')->value('id');
        if ($cookiesId === null) {
            $cookiesId = DB::table('pages')->insertGetId([
                'page_key' => 'cookies',
                'image' => null,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]);
        }

        $this->upsertTranslation($cookiesId, 'en', [
            'name' => 'Cookies',
            'slug' => 'cookies',
            'key_words' => 'cookies',
            'description' => '<p>This website uses cookies to improve your experience. Edit this page from the dashboard to publish the full cookie policy.</p>',
        ]);
        $this->upsertTranslation($cookiesId, 'ar', [
            'name' => 'الكوكيز',
            'slug' => 'الكوكيز',
            'key_words' => 'كوكيز',
            'description' => '<p>يستخدم هذا الموقع ملفات تعريف الارتباط لتحسين تجربتك. عدّل هذه الصفحة من لوحة التحكم لنشر سياسة الكوكيز الكاملة.</p>',
        ]);
    }

    private function upsertTranslation(int $pageId, string $locale, array $row): void
    {
        $exists = DB::table('page_translations')
            ->where('page_id', $pageId)
            ->where('locale', $locale)
            ->exists();

        if ($exists) {
            // Keep existing edited content; only fill empty descriptions
            $current = DB::table('page_translations')
                ->where('page_id', $pageId)
                ->where('locale', $locale)
                ->first();
            if ($current && trim((string) $current->description) === '') {
                DB::table('page_translations')
                    ->where('page_id', $pageId)
                    ->where('locale', $locale)
                    ->update(['description' => $row['description']]);
            }

            return;
        }

        DB::table('page_translations')->insert([
            'page_id' => $pageId,
            'locale' => $locale,
            'name' => $row['name'],
            'slug' => $row['slug'],
            'key_words' => $row['key_words'],
            'description' => $row['description'],
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('pages') || ! Schema::hasColumn('pages', 'page_key')) {
            return;
        }

        $id = DB::table('pages')->where('page_key', 'cookies')->value('id');
        if ($id) {
            DB::table('page_translations')->where('page_id', $id)->delete();
            DB::table('pages')->where('id', $id)->delete();
        }
    }
};
