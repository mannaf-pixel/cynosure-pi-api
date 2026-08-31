<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('sinewy_white_rate', 10, 2)->default(0)->after('color_rate');
            $table->decimal('sinewy_color_rate', 10, 2)->default(0)->after('sinewy_white_rate');
            $table->decimal('assre_white_rate', 10, 2)->default(0)->after('sinewy_color_rate');
            $table->decimal('assre_color_rate', 10, 2)->default(0)->after('assre_white_rate');
        });
    }
    public function down(): void {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['sinewy_white_rate', 'sinewy_color_rate', 'assre_white_rate', 'assre_color_rate']);
        });
    }
};
