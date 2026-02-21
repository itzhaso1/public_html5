<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'money_exchange_enabled')) {
                $table->boolean('money_exchange_enabled')->default(true);
            }
            if (!Schema::hasColumn('settings', 'charge_enabled')) {
                $table->boolean('charge_enabled')->default(true);
            }
            if (!Schema::hasColumn('settings', 'codes_enabled')) {
                $table->boolean('codes_enabled')->default(true);
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('settings', 'money_exchange_enabled')) $cols[] = 'money_exchange_enabled';
            if (Schema::hasColumn('settings', 'charge_enabled')) $cols[] = 'charge_enabled';
            if (Schema::hasColumn('settings', 'codes_enabled')) $cols[] = 'codes_enabled';
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};

