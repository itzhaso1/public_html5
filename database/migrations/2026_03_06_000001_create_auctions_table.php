<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignId('winner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('game_name');
            $table->longText('description');
            $table->decimal('starting_price', 12, 2);
            $table->decimal('current_price', 12, 2)->nullable();
            $table->decimal('final_price', 12, 2)->nullable();
            $table->decimal('subscription_fee', 12, 2)->default(0);
            $table->decimal('bid_increment', 12, 2)->default(1);
            $table->unsignedInteger('min_participants')->default(15);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'active', 'paused', 'ended', 'cancelled'])->default('scheduled');
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->index(['status', 'starts_at']);
            $table->index(['status', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};
