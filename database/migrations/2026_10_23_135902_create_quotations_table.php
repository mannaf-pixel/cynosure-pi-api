<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_number')->unique();
            $table->foreignId('company_id')->constrained('companies');
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('created_by')->constrained('users');
            $table->string('brand')->default('cynosure');
            $table->string('salesperson_name')->nullable();
            $table->string('color_name')->nullable();
            $table->decimal('white_discount_pct', 5, 2)->default(0);
            $table->decimal('color_discount_pct', 5, 2)->default(0);
            $table->decimal('hardware_discount_pct', 5, 2)->default(0);
            $table->decimal('cd_discount_pct', 5, 2)->default(0);
            $table->decimal('cd_discount_amount', 10, 2)->default(0);
            $table->decimal('transport_charge', 10, 2)->default(0);
            $table->decimal('insurance_pct', 5, 3)->default(0);
            $table->decimal('insurance_charge', 10, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('gst_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->enum('status', ['draft', 'sent', 'confirmed', 'converted', 'expired', 'cancelled'])->default('draft');
            $table->date('valid_until')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('pi_id')->nullable()->constrained('pi_master');
            $table->timestamps();
        });

        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->onDelete('cascade');
            $table->enum('item_type', ['profile', 'hardware'])->default('profile');
            $table->foreignId('product_id')->nullable()->constrained('products');
            $table->foreignId('hardware_product_id')->nullable()->constrained('hardware_products');
            $table->string('product_code_snap')->nullable();
            $table->string('product_name_snap')->nullable();
            $table->string('hardware_name_snap')->nullable();
            $table->string('hardware_unit_snap')->nullable();
            $table->string('profile_type_snap')->nullable();
            $table->string('color_name')->nullable();
            $table->decimal('unit_rate_snap', 10, 2)->default(0);
            $table->decimal('mrp_rate_snap', 10, 2)->default(0);
            $table->decimal('item_discount_pct', 5, 2)->default(0);
            $table->decimal('profile_length_snap', 8, 2)->default(0);
            $table->decimal('weight_per_meter_snap', 8, 3)->default(0);
            $table->integer('bundle_qty_ordered')->default(0);
            $table->decimal('total_length', 10, 2)->default(0);
            $table->integer('total_pieces')->default(0);
            $table->decimal('total_weight', 10, 3)->default(0);
            $table->decimal('quantity', 10, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
    }
};
