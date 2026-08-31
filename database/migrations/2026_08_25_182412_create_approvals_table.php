<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pi_id')->constrained('pi_master')->onDelete('cascade');
            $table->foreignId('approver_id')->constrained('users')->onDelete('restrict');
            $table->enum('action', ['approved', 'rejected', 'held', 'commented']);
            $table->enum('approver_role', ['md', 'ceo']);
            $table->text('comments')->nullable();
            $table->timestamp('actioned_at')->useCurrent();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('approvals');
    }
};