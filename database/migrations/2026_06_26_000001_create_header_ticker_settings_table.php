<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('header_ticker_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(true);
            $table->string('prefix_en', 500)->nullable();
            $table->string('prefix_ar', 500)->nullable();
            $table->string('cta_en', 120)->nullable();
            $table->string('cta_ar', 120)->nullable();
            $table->string('link_en', 500)->nullable();
            $table->string('link_ar', 500)->nullable();
            $table->timestamps();
        });

        $now = now();
        DB::table('header_ticker_settings')->insert([
            'is_enabled' => true,
            'prefix_en' => '>> KEEPERsport Saudi Arabia | Goalkeeper specialists |',
            'prefix_ar' => '>> KEEPERsport السعودية | متخصصون في حراس المرمى |',
            'cta_en' => 'Shop now',
            'cta_ar' => 'تسوق الآن',
            'link_en' => '/sale',
            'link_ar' => '/sale',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('header_ticker_settings');
    }
};
