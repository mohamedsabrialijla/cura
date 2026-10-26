<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_technic_section_settings', function (Blueprint $table) {
            $table->id();
            $table->string('title_white_ar')->nullable();
            $table->string('title_gold_ar')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('title_white_en')->nullable();
            $table->string('title_gold_en')->nullable();
            $table->text('description_en')->nullable();
            $table->timestamps();
        });

        $now = now();
        DB::table('landing_technic_section_settings')->insert([
            'title_white_ar' => 'ملابس',
            'title_gold_ar' => 'تقنية',
            'description_ar' => 'مواد عالية الأداء، وتقنيات مدروسة بدقة، وأنظمة حماية متطورة: كل مجموعة مصممة لتمنحك تحكمًا وراحة وأقصى أمان في أي ظروف لعب.',
            'title_white_en' => 'TECHNICAL',
            'title_gold_en' => 'APPAREL',
            'description_en' => 'High-performance materials, carefully engineered technologies and advanced protection systems: every collection is designed to give you control, comfort and maximum safety in any playing conditions.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_technic_section_settings');
    }
};
