<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('wallet_topup_requests')) {
            Schema::table('wallet_topup_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('wallet_topup_requests', 'payment_method')) {
                    $table->string('payment_method', 50)->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('wallet_topup_requests')) {
            Schema::table('wallet_topup_requests', function (Blueprint $table) {
                if (Schema::hasColumn('wallet_topup_requests', 'payment_method')) {
                    $table->dropColumn('payment_method');
                }
            });
        }
    }
};

