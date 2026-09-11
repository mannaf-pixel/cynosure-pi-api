<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pi_master', function (Blueprint $table) {
            $table->enum('stock_status', ['ready', 'partial', 'not_available'])->nullable()->after('expected_dispatch_date');
            $table->enum('production_status', ['ready', 'in_production', 'pending'])->nullable()->after('stock_status');
            $table->text('schedule_remarks')->nullable()->after('production_status');
        });
    }

    public function down(): void
    {
        Schema::table('pi_master', function (Blueprint $table) {
            $table->dropColumn(['stock_status', 'production_status', 'schedule_remarks']);
        });
    }
};
