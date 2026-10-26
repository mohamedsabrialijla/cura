<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shopping_carts', function (Blueprint $table) {
            $table->id();
            $table->string('cart_token', 64)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('items_count')->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->string('currency', 8)->default('EUR');
            $table->string('status', 32)->default('active');
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'last_activity_at']);
        });

        Schema::create('shopping_cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shopping_cart_id')->constrained()->cascadeOnDelete();
            $table->string('product_key', 128);
            $table->string('sku', 128)->nullable();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->unsignedInteger('qty')->default(1);
            $table->text('image')->nullable();
            $table->timestamps();

            $table->index(['shopping_cart_id', 'product_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopping_cart_items');
        Schema::dropIfExists('shopping_carts');
    }
};
