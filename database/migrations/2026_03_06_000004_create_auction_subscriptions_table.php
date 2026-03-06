<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auction_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_id')->constrained('auctions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->foreignId('reviewed_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('receipt_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'refunded', 'applied_to_winner'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['auction_id', 'user_id']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_subscriptions');
    }
};
