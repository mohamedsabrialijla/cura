<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->decimal('price_markup_percent', 8, 2)->default(0)->after('vat');
            $table->decimal('eur_to_sar_rate', 12, 4)->default(0)->after('price_markup_percent');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['price_markup_percent', 'eur_to_sar_rate']);
        });
    }
};
