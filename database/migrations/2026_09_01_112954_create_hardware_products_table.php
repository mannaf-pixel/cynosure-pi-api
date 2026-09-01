<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hardware_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable();
            $table->enum('unit', ['piece', 'meter', 'set', 'kg'])->default('piece');
            $table->decimal('rate', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // pi_items mein item_type column add karo
        Schema::table('pi_items', function (Blueprint $table) {
            $table->enum('item_type', ['profile', 'hardware'])->default('profile')->after('pi_id');
            $table->foreignId('hardware_product_id')->nullable()->after('product_id');
            $table->string('hardware_name_snap')->nullable()->after('hardware_product_id');
            $table->string('hardware_unit_snap')->nullable()->after('hardware_name_snap');
            $table->decimal('quantity', 10, 3)->default(0)->after('hardware_unit_snap');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hardware_products');
        Schema::table('pi_items', function (Blueprint $table) {
            $table->dropColumn(['item_type', 'hardware_product_id', 'hardware_name_snap', 'hardware_unit_snap', 'quantity']);
        });
    }
};
