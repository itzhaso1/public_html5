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
            if (!Schema::hasColumn('settings', 'home_quick_freefire_title')) {
                $table->string('home_quick_freefire_title')->nullable()->after('home_quick_money_exchange_title');
            }
            if (!Schema::hasColumn('settings', 'home_quick_freefire_position')) {
                $table->string('home_quick_freefire_position', 20)->default('end')->after('home_quick_freefire_title');
            }
            if (!Schema::hasColumn('settings', 'home_featured_product_ids_all')) {
                $table->longText('home_featured_product_ids_all')->nullable()->after('home_featured_product_ids');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'home_featured_product_ids_all')) {
                $table->dropColumn('home_featured_product_ids_all');
            }
            if (Schema::hasColumn('settings', 'home_quick_freefire_position')) {
                $table->dropColumn('home_quick_freefire_position');
            }
            if (Schema::hasColumn('settings', 'home_quick_freefire_title')) {
                $table->dropColumn('home_quick_freefire_title');
            }
        });
    }
};

