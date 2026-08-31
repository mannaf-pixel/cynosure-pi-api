<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pricing_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->enum('profile_type', ['white', 'color']);
            $table->decimal('old_rate', 10, 2);
            $table->decimal('new_rate', 10, 2);
            $table->foreignId('changed_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('changed_at')->useCurrent();
        });
    }
    public function down(): void {
        Schema::dropIfExists('pricing_history');
    }
};