<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faq_items', function (Blueprint $table) {
            $table->id();
            $table->string('category', 120);
            $table->string('question_ar', 500);
            $table->string('question_en', 500);
            $table->text('answer_ar');
            $table->text('answer_en');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('status', 32)->default('active');
            $table->timestamps();

            $table->index(['category', 'status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faq_items');
    }
};
