<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('header_ticker_settings')) {
            return;
        }

        if (Schema::hasColumn('header_ticker_settings', 'bg_color')) {
            return;
        }

        Schema::table('header_ticker_settings', function (Blueprint $table) {
            $table->string('bg_color', 20)->default('#000000')->after('link_ar');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('header_ticker_settings')) {
            return;
        }

        if (! Schema::hasColumn('header_ticker_settings', 'bg_color')) {
            return;
        }

        Schema::table('header_ticker_settings', function (Blueprint $table) {
            $table->dropColumn('bg_color');
        });
    }
};
