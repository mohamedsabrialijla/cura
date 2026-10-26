<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('title_ar', 255);
            $table->string('title_en', 255);
            $table->string('description_ar', 500)->nullable();
            $table->string('description_en', 500)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('status', 32)->default('active');
            $table->timestamps();

            $table->index(['status', 'sort_order']);
        });

        DB::table('payment_methods')->insert([
            'code' => 'neoleap',
            'title_ar' => 'NeoLeap',
            'title_en' => 'NeoLeap',
            'description_ar' => 'بطاقة مدى / فيزا / ماستركارد',
            'description_en' => 'Mada / Visa / Mastercard',
            'sort_order' => 1,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
