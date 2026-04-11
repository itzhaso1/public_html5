<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'custom_usd_to_sar_rate')) {
                // Admin-friendly format: 1 USD = X SAR (e.g. 3.900000)
                $table->decimal('custom_usd_to_sar_rate', 10, 6)->nullable()->after('merchant_usd_rate');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'custom_usd_to_sar_rate')) {
                $table->dropColumn('custom_usd_to_sar_rate');
            }
        });
    }
};
