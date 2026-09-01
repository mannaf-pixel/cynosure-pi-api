<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pi_master', function (Blueprint $table) {
            $table->timestamp('delivery_scheduled_at')->nullable()->after('expected_dispatch_date');
        });

        // Update enum - MySQL mein status column modify karo
        DB::statement("ALTER TABLE pi_master MODIFY COLUMN status ENUM('draft','md_pending','ceo_pending','ceo_approved','payment_confirmed','delivery_scheduled','dispatched','rejected') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE pi_master MODIFY COLUMN status ENUM('draft','md_pending','ceo_pending','ceo_approved','payment_confirmed','dispatched','rejected') NOT NULL DEFAULT 'draft'");
        Schema::table('pi_master', function (Blueprint $table) {
            $table->dropColumn('delivery_scheduled_at');
        });
    }
};
