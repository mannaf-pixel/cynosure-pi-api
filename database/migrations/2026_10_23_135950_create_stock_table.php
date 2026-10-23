<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies');
            $table->enum('item_type', ['profile', 'hardware']);
            $table->foreignId('product_id')->nullable()->constrained('products');
            $table->foreignId('hardware_product_id')->nullable()->constrained('hardware_products');
            $table->string('item_code');
            $table->string('item_name');
            $table->decimal('quantity', 12, 3)->default(0);
            $table->string('unit')->default('bundle');
            $table->decimal('low_stock_alert', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained('stock')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies');
            $table->enum('type', ['in', 'out', 'adjustment']);
            $table->decimal('quantity', 12, 3);
            $table->decimal('quantity_before', 12, 3)->default(0);
            $table->decimal('quantity_after', 12, 3)->default(0);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('note')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
        Schema::dropIfExists('stock');
    }
};
