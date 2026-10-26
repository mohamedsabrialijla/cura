<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('full_name', 255);
            $table->string('email', 255)->nullable();
            $table->string('mobile', 32);
            $table->string('country_code', 8)->default('SA');
            $table->string('country_name', 120)->nullable();
            $table->string('city', 120);
            $table->string('district', 120)->nullable();
            $table->string('street', 255);
            $table->string('building', 120)->nullable();
            $table->string('apartment', 120)->nullable();
            $table->string('postal_code', 32)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_default']);
            $table->index('country_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};
