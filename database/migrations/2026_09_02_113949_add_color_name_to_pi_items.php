<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pi_items', function (Blueprint $table) {
            $table->string('color_name')->nullable()->after('profile_type_snap');
        });
    }

    public function down(): void
    {
        Schema::table('pi_items', function (Blueprint $table) {
            $table->dropColumn('color_name');
        });
    }
};
