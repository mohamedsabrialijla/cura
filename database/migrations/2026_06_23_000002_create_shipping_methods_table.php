<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->string('title_ar', 255);
            $table->string('title_en', 255);
            $table->unsignedSmallInteger('delivery_days')->default(1);
            $table->decimal('price', 12, 2)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('status', 32)->default('active');
            $table->timestamps();

            $table->index(['status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
    }
};
