<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pi_master', function (Blueprint $table) {
            $table->date('expected_dispatch_date')->nullable()->after('dispatched_at');
        });
    }

    public function down(): void
    {
        Schema::table('pi_master', function (Blueprint $table) {
            $table->dropColumn('expected_dispatch_date');
        });
    }
};
