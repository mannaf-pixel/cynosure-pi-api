<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('pi_master', function (Blueprint $table) {
            $table->decimal('discount_pct', 5, 2)->default(0)->after('insurance_charge');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('discount_pct');
        });
    }
    public function down(): void {
        Schema::table('pi_master', function (Blueprint $table) {
            $table->dropColumn(['discount_pct', 'discount_amount']);
        });
    }
};