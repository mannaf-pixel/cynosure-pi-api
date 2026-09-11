<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pi_master', function (Blueprint $table) {
            $table->decimal('white_discount_pct', 5, 2)->default(0)->after('discount_pct');
            $table->decimal('color_discount_pct', 5, 2)->default(0)->after('white_discount_pct');
            $table->decimal('hardware_discount_pct', 5, 2)->default(0)->after('color_discount_pct');
            $table->decimal('cd_discount_pct', 5, 2)->default(0)->after('hardware_discount_pct');
            $table->decimal('cd_discount_amount', 10, 2)->default(0)->after('cd_discount_pct');
        });

        Schema::table('pi_items', function (Blueprint $table) {
            $table->decimal('item_discount_pct', 5, 2)->default(0)->after('color_name');
        });
    }

    public function down(): void
    {
        Schema::table('pi_master', function (Blueprint $table) {
            $table->dropColumn(['white_discount_pct','color_discount_pct','hardware_discount_pct','cd_discount_pct','cd_discount_amount']);
        });
        Schema::table('pi_items', function (Blueprint $table) {
            $table->dropColumn('item_discount_pct');
        });
    }
};
