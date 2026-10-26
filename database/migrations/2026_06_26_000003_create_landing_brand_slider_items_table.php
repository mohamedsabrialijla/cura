<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_brand_slider_items', function (Blueprint $table) {
            $table->id();
            $table->string('title_ar')->nullable();
            $table->string('title_en')->nullable();
            $table->string('link', 500)->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('sort_order');
        });

        $now = now();
        $defaults = [
            ['title_en' => 'adidas', 'title_ar' => 'adidas', 'link' => '/shop/adidas-goalkeeper-gloves', 'image' => 'assets_website/brand/a.webp'],
            ['title_en' => 'PUMA', 'title_ar' => 'PUMA', 'link' => '/shop/puma-goalkeeper-gloves', 'image' => 'assets_website/brand/b.webp'],
            ['title_en' => 'NIKE', 'title_ar' => 'NIKE', 'link' => '/shop/nike-goalkeeper-gloves', 'image' => 'assets_website/brand/c.webp'],
            ['title_en' => 'KEEPERsport', 'title_ar' => 'KEEPERsport', 'link' => '/shop/keepersport-goalkeeper-gloves', 'image' => 'assets_website/brand/d.webp'],
            ['title_en' => 'uhlsport', 'title_ar' => 'uhlsport', 'link' => '/shop/uhlsport-goalkeeper-gloves', 'image' => 'assets_website/brand/e.webp'],
            ['title_en' => 'reusch', 'title_ar' => 'reusch', 'link' => '/shop/reusch-goalkeeper-gloves', 'image' => 'assets_website/brand/f.webp'],
        ];

        $rows = [];
        foreach ($defaults as $index => $row) {
            $rows[] = [
                'title_ar' => $row['title_ar'],
                'title_en' => $row['title_en'],
                'link' => $row['link'],
                'image' => $row['image'],
                'sort_order' => $index,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('landing_brand_slider_items')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_brand_slider_items');
    }
};
