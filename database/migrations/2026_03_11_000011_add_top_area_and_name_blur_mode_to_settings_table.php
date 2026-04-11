<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'account_name_blur_mode')) {
                $table->string('account_name_blur_mode', 20)->default('adaptive');
            }

            if (!Schema::hasColumn('settings', 'account_top_area_mode')) {
                $table->string('account_top_area_mode', 20)->default('blur');
            }
            if (!Schema::hasColumn('settings', 'account_top_area_size_px')) {
                $table->integer('account_top_area_size_px')->default(35);
            }
            if (!Schema::hasColumn('settings', 'account_top_area_width_px')) {
                $table->integer('account_top_area_width_px')->default(0);
            }
            if (!Schema::hasColumn('settings', 'account_top_area_x_from_right_px')) {
                $table->integer('account_top_area_x_from_right_px')->default(0);
            }
            if (!Schema::hasColumn('settings', 'account_top_area_blur_strength')) {
                $table->integer('account_top_area_blur_strength')->default(35);
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $columns = [
                'account_name_blur_mode',
                'account_top_area_mode',
                'account_top_area_size_px',
                'account_top_area_width_px',
                'account_top_area_x_from_right_px',
                'account_top_area_blur_strength',
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

