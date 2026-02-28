<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Users: merchant flag
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'is_merchant')) {
                    $table->boolean('is_merchant')->default(false);
                }
            });
        }

        // Settings: merchant USD rate override (SAR -> USD)
        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                if (!Schema::hasColumn('settings', 'merchant_usd_rate')) {
                    $table->decimal('merchant_usd_rate', 10, 6)->nullable();
                }
            });
        }

        // Merchant requests
        if (!Schema::hasTable('merchant_requests')) {
            Schema::create('merchant_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('name')->nullable();
                $table->string('phone', 32)->nullable();
                $table->text('note')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('merchant_requests')) {
            Schema::dropIfExists('merchant_requests');
        }

        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                if (Schema::hasColumn('settings', 'merchant_usd_rate')) {
                    $table->dropColumn('merchant_usd_rate');
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'is_merchant')) {
                    $table->dropColumn('is_merchant');
                }
            });
        }
    }
};

