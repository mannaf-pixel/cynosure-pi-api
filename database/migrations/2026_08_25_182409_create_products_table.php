<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_code', 20)->unique();
            $table->string('product_name');
            $table->string('category')->nullable();
            $table->decimal('white_rate', 10, 2)->default(0);
            $table->decimal('color_rate', 10, 2)->default(0);
            $table->string('unit', 20)->default('meter');
            $table->decimal('profile_length', 5, 2)->default(5.80);
            $table->integer('bundle_qty')->default(1);
            $table->decimal('mrp', 10, 2)->default(0);
            $table->decimal('discount_pct', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('products');
    }
};