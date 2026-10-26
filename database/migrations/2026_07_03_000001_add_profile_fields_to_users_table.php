<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'logo')) {
                $table->string('logo', 500)->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'mobile')) {
                $table->string('mobile', 32)->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'pwd')) {
                $table->string('pwd', 255)->nullable()->after('password');
            }
            if (! Schema::hasColumn('users', 'gender')) {
                $table->string('gender', 16)->nullable()->default('male');
            }
            if (! Schema::hasColumn('users', 'status')) {
                $table->string('status', 32)->nullable()->default('active');
            }
            if (! Schema::hasColumn('users', 'verification')) {
                $table->unsignedTinyInteger('verification')->default(1);
            }
            if (! Schema::hasColumn('users', 'country_id')) {
                $table->unsignedBigInteger('country_id')->nullable();
            }
            if (! Schema::hasColumn('users', 'city_id')) {
                $table->unsignedBigInteger('city_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['logo', 'mobile', 'pwd', 'gender', 'status', 'verification', 'country_id', 'city_id'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
