<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('pi_items', function (Blueprint $table) {
            $table->decimal('weight_per_meter_snap', 8, 3)->default(0)->after('profile_length_snap');
            $table->decimal('total_weight', 8, 3)->default(0)->after('total_pieces');
        });
    }
    public function down(): void {
        Schema::table('pi_items', function (Blueprint $table) {
            $table->dropColumn(['weight_per_meter_snap', 'total_weight']);
        });
    }
};