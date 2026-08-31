<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pi_master', function (Blueprint $table) {
            $table->id();
            $table->string('pi_number', 20)->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('restrict');
            $table->enum('profile_type', ['white', 'color'])->default('white');
            $table->enum('status', [
                'draft',
                'submitted',
                'md_pending',
                'md_approved',
                'ceo_pending',
                'ceo_approved',
                'rejected'
            ])->default('draft');
            $table->decimal('transport_charge', 10, 2)->default(0);
            $table->decimal('insurance_charge', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('gst_amount', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('pi_master');
    }
};