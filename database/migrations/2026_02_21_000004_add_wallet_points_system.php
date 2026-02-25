<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Users wallet balance
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'wallet_points_balance')) {
                    $table->unsignedBigInteger('wallet_points_balance')->default(0);
                }
            });
        }

        // Settings: point price
        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                if (!Schema::hasColumn('settings', 'point_price_sar')) {
                    $table->decimal('point_price_sar', 10, 2)->default(3.75);
                }
                if (!Schema::hasColumn('settings', 'point_price_usd')) {
                    $table->decimal('point_price_usd', 10, 2)->default(1.00);
                }
            });
        }

        // Products: points price
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'points_price')) {
                    $table->unsignedInteger('points_price')->nullable();
                }
            });
        }

        // Manual payment requests: store points spend & refunds (for points purchases)
        if (Schema::hasTable('manual_payment_requests')) {
            Schema::table('manual_payment_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('manual_payment_requests', 'points_spent')) {
                    $table->unsignedInteger('points_spent')->nullable();
                }
                if (!Schema::hasColumn('manual_payment_requests', 'points_refunded_at')) {
                    $table->timestamp('points_refunded_at')->nullable();
                }
            });
        }

        // Wallet transactions
        if (!Schema::hasTable('wallet_transactions')) {
            Schema::create('wallet_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('type', 50); // deposit_credit, purchase_debit, refund_credit, admin_adjust, ...
                $table->integer('points_delta'); // can be negative
                $table->unsignedBigInteger('balance_before');
                $table->unsignedBigInteger('balance_after');
                $table->string('reference_type')->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
                $table->index(['user_id', 'created_at']);
            });
        }

        // Wallet top-up requests (manual review)
        if (!Schema::hasTable('wallet_topup_requests')) {
            Schema::create('wallet_topup_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedInteger('points');
                $table->decimal('point_price_sar', 10, 2);
                $table->decimal('point_price_usd', 10, 2);
                $table->decimal('amount_sar', 10, 2);
                $table->decimal('amount_usd', 10, 2);
                $table->string('receipt_path')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->text('admin_note')->nullable();
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
                $table->index(['user_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('wallet_topup_requests')) {
            Schema::dropIfExists('wallet_topup_requests');
        }
        if (Schema::hasTable('wallet_transactions')) {
            Schema::dropIfExists('wallet_transactions');
        }

        if (Schema::hasTable('manual_payment_requests')) {
            Schema::table('manual_payment_requests', function (Blueprint $table) {
                $cols = [];
                if (Schema::hasColumn('manual_payment_requests', 'points_spent')) $cols[] = 'points_spent';
                if (Schema::hasColumn('manual_payment_requests', 'points_refunded_at')) $cols[] = 'points_refunded_at';
                if (!empty($cols)) $table->dropColumn($cols);
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'points_price')) {
                    $table->dropColumn('points_price');
                }
            });
        }

        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                $cols = [];
                if (Schema::hasColumn('settings', 'point_price_sar')) $cols[] = 'point_price_sar';
                if (Schema::hasColumn('settings', 'point_price_usd')) $cols[] = 'point_price_usd';
                if (!empty($cols)) $table->dropColumn($cols);
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'wallet_points_balance')) {
                    $table->dropColumn('wallet_points_balance');
                }
            });
        }
    }
};

