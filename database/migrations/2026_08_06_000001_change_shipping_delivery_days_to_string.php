<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('shipping_methods') || ! Schema::hasColumn('shipping_methods', 'delivery_days')) {
            return;
        }

        // Allow text like "5-7 أيام عمل" (avoid doctrine/dbal change())
        DB::statement("ALTER TABLE `shipping_methods` MODIFY `delivery_days` VARCHAR(120) NOT NULL DEFAULT '1'");
    }

    public function down(): void
    {
        if (! Schema::hasTable('shipping_methods') || ! Schema::hasColumn('shipping_methods', 'delivery_days')) {
            return;
        }

        $rows = DB::table('shipping_methods')->select('id', 'delivery_days')->get();
        foreach ($rows as $row) {
            if (preg_match('/(\d+)/', (string) $row->delivery_days, $m)) {
                $n = max(1, min(365, (int) $m[1]));
            } else {
                $n = 1;
            }
            DB::table('shipping_methods')->where('id', $row->id)->update([
                'delivery_days' => $n,
            ]);
        }

        DB::statement('ALTER TABLE `shipping_methods` MODIFY `delivery_days` SMALLINT UNSIGNED NOT NULL DEFAULT 1');
    }
};
