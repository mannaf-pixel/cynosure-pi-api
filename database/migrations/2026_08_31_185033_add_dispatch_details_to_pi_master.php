<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pi_master', function (Blueprint $table) {
            $table->string('transport_company')->nullable()->after('remarks');
            $table->string('vehicle_number')->nullable()->after('transport_company');
            $table->string('driver_name')->nullable()->after('vehicle_number');
            $table->string('driver_phone')->nullable()->after('driver_name');
            $table->string('lr_number')->nullable()->after('driver_phone');
            $table->decimal('freight_amount', 10, 2)->nullable()->after('lr_number');
            $table->text('dispatch_note')->nullable()->after('freight_amount');
            $table->string('dispatch_photo')->nullable()->after('dispatch_note');
            $table->string('transport_copy')->nullable()->after('dispatch_photo');
            $table->timestamp('dispatched_at')->nullable()->after('transport_copy');
        });
    }

    public function down(): void
    {
        Schema::table('pi_master', function (Blueprint $table) {
            $table->dropColumn([
                'transport_company', 'vehicle_number', 'driver_name',
                'driver_phone', 'lr_number', 'freight_amount',
                'dispatch_note', 'dispatch_photo', 'transport_copy', 'dispatched_at'
            ]);
        });
    }
};
