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
            if (!Schema::hasColumn('settings', 'home_featured_all_autoplay_seconds')) {
                $table->unsignedTinyInteger('home_featured_all_autoplay_seconds')
                    ->default(3)
                    ->after('home_featured_product_ids_all');
            }
            if (!Schema::hasColumn('settings', 'home_featured_all_mobile_columns')) {
                $table->unsignedTinyInteger('home_featured_all_mobile_columns')
                    ->default(1)
                    ->after('home_featured_all_autoplay_seconds');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'home_featured_all_mobile_columns')) {
                $table->dropColumn('home_featured_all_mobile_columns');
            }
            if (Schema::hasColumn('settings', 'home_featured_all_autoplay_seconds')) {
                $table->dropColumn('home_featured_all_autoplay_seconds');
            }
        });
    }
};

