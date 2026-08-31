<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('pi_master', function (Blueprint $table) {
            $table->enum('brand', ['assre_plasto', 'cynosure', 'sinewy'])->default('cynosure')->after('pi_number');
        });
    }
    public function down(): void {
        Schema::table('pi_master', function (Blueprint $table) {
            $table->dropColumn('brand');
        });
    }
};