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

        $key = 'goalkeeper-day';
        $now = now();
        $pageId = DB::table('pages')->where('page_key', $key)->value('id');
        if ($pageId === null) {
            $pageId = DB::table('pages')->insertGetId([
                'page_key' => $key,
                'image' => null,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]);
        }

        $locales = [
            'en' => [
                'name' => 'Goalkeeper Day',
                'slug' => 'goalkeeper-day',
                'key_words' => 'goalkeeper day, keepersport',
                'description' => '<p>Goalkeeper Day celebrates every keeper — amateurs and pros. Edit this page from the dashboard to publish the full event content, schedule, and media.</p>',
            ],
            'ar' => [
                'name' => 'يوم حارس المرمى',
                'slug' => 'يوم-حارس-المرمى',
                'key_words' => 'يوم حارس المرمى',
                'description' => '<p>يوم حارس المرمى يحتفل بكل حارس — هواة ومحترفين. عدّل هذه الصفحة من لوحة التحكم لنشر محتوى الفعالية والجدول والوسائط.</p>',
            ],
        ];

        foreach ($locales as $locale => $row) {
            $exists = DB::table('page_translations')
                ->where('page_id', $pageId)
                ->where('locale', $locale)
                ->exists();

            if ($exists) {
                DB::table('page_translations')
                    ->where('page_id', $pageId)
                    ->where('locale', $locale)
                    ->update([
                        'name' => $row['name'],
                        'slug' => $row['slug'],
                        'key_words' => $row['key_words'],
                        'description' => $row['description'],
                    ]);
            } else {
                DB::table('page_translations')->insert([
                    'page_id' => $pageId,
                    'locale' => $locale,
                    'name' => $row['name'],
                    'slug' => $row['slug'],
                    'key_words' => $row['key_words'],
                    'description' => $row['description'],
                ]);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('pages') || ! Schema::hasColumn('pages', 'page_key')) {
            return;
        }

        $id = DB::table('pages')->where('page_key', 'goalkeeper-day')->value('id');
        if (! $id) {
            return;
        }

        DB::table('page_translations')->where('page_id', $id)->delete();
        DB::table('pages')->where('id', $id)->delete();
    }
};
