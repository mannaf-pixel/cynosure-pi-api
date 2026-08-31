<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pi_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pi_id')->constrained('pi_master')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->string('product_code_snap', 20);
            $table->string('product_name_snap');
            $table->decimal('unit_rate_snap', 10, 2);
            $table->decimal('profile_length_snap', 5, 2);
            $table->integer('bundle_qty_ordered');
            $table->decimal('total_length', 10, 2);
            $table->integer('total_pieces');
            $table->decimal('line_total', 10, 2);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('pi_items');
    }
};