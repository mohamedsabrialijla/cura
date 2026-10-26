<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FaqItemsSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('faq_items')) {
            $this->command?->error('faq_items table missing. Run migration first.');

            return;
        }

        $rows = require database_path('data/faq_items_seed.php');
        if (! is_array($rows) || $rows === []) {
            $this->command?->error('faq seed data empty.');

            return;
        }

        DB::table('faq_items')->delete();

        $now = now();
        $chunk = [];
        foreach ($rows as $row) {
            $chunk[] = [
                'category' => $row['category'],
                'question_ar' => $row['question_ar'],
                'question_en' => $row['question_en'],
                'answer_ar' => $row['answer_ar'],
                'answer_en' => $row['answer_en'],
                'sort_order' => (int) ($row['sort_order'] ?? 0),
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($chunk, 50) as $part) {
            DB::table('faq_items')->insert($part);
        }

        $this->command?->info('Seeded '.count($chunk).' FAQ items.');
    }
}
