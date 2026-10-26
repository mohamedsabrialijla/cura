<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'instagram')) {
                $table->string('instagram', 500)->nullable()->after('whatsapp');
            }
            if (! Schema::hasColumn('settings', 'facebook')) {
                $table->string('facebook', 500)->nullable()->after('instagram');
            }
            if (! Schema::hasColumn('settings', 'tiktok')) {
                $table->string('tiktok', 500)->nullable()->after('facebook');
            }
            if (! Schema::hasColumn('settings', 'snapchat')) {
                $table->string('snapchat', 500)->nullable()->after('tiktok');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $cols = array_filter([
                Schema::hasColumn('settings', 'instagram') ? 'instagram' : null,
                Schema::hasColumn('settings', 'facebook') ? 'facebook' : null,
                Schema::hasColumn('settings', 'tiktok') ? 'tiktok' : null,
                Schema::hasColumn('settings', 'snapchat') ? 'snapchat' : null,
            ]);
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });
    }
};
