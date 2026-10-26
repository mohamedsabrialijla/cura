<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pages')) {
            return;
        }

        if (! Schema::hasColumn('pages', 'page_key')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->string('page_key', 32)->nullable()->unique()->after('id');
            });
        }

        DB::table('pages')->where('id', 4)->update(['page_key' => 'terms']);

        $privacyId = DB::table('pages')->where('page_key', 'privacy')->value('id');
        if ($privacyId === null) {
            $privacyId = DB::table('pages')->where('id', 5)->value('id');
        }

        if ($privacyId === null) {
            $now = now();
            $privacyId = DB::table('pages')->insertGetId([
                'page_key' => 'privacy',
                'image' => null,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]);

            foreach (['en', 'ar'] as $locale) {
                DB::table('page_translations')->insert([
                    'page_id' => $privacyId,
                    'locale' => $locale,
                    'name' => $locale === 'ar' ? 'سياسة الخصوصية' : 'Privacy Policy',
                    'slug' => $locale === 'ar' ? 'privacy-policy' : 'privacy-policy',
                    'description' => $locale === 'ar'
                        ? '<p>أضف نص سياسة الخصوصية من لوحة التحكم.</p>'
                        : '<p>Add your privacy policy content from the dashboard.</p>',
                    'key_words' => $locale === 'ar' ? 'خصوصية' : 'privacy',
                ]);
            }
        } else {
            DB::table('pages')->where('id', $privacyId)->update(['page_key' => 'privacy']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pages') && Schema::hasColumn('pages', 'page_key')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->dropColumn('page_key');
            });
        }
    }
};
