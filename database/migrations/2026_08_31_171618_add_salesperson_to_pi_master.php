<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('pi_master', function (Blueprint $table) {
            $table->foreignId('salesperson_id')->nullable()->after('created_by')
                  ->constrained('users')->onDelete('set null');
        });
    }
    public function down(): void {
        Schema::table('pi_master', function (Blueprint $table) {
            $table->dropForeign(['salesperson_id']);
            $table->dropColumn('salesperson_id');
        });
    }
};
