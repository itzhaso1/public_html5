<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'account_name_blur_enabled')) {
                $table->boolean('account_name_blur_enabled')->default(true);
            }
            if (!Schema::hasColumn('settings', 'account_name_blur_x_offset_from_right')) {
                $table->integer('account_name_blur_x_offset_from_right')->default(420);
            }
            if (!Schema::hasColumn('settings', 'account_name_blur_y')) {
                $table->integer('account_name_blur_y')->default(40);
            }
            if (!Schema::hasColumn('settings', 'account_name_blur_width')) {
                $table->integer('account_name_blur_width')->default(350);
            }
            if (!Schema::hasColumn('settings', 'account_name_blur_height')) {
                $table->integer('account_name_blur_height')->default(100);
            }
            if (!Schema::hasColumn('settings', 'account_name_blur_strength')) {
                $table->integer('account_name_blur_strength')->default(35);
            }

            if (!Schema::hasColumn('settings', 'account_center_blur_enabled')) {
                $table->boolean('account_center_blur_enabled')->default(false);
            }
            if (!Schema::hasColumn('settings', 'account_center_blur_x')) {
                $table->integer('account_center_blur_x')->default(0);
            }
            if (!Schema::hasColumn('settings', 'account_center_blur_y')) {
                $table->integer('account_center_blur_y')->default(0);
            }
            if (!Schema::hasColumn('settings', 'account_center_blur_width')) {
                $table->integer('account_center_blur_width')->default(120);
            }
            if (!Schema::hasColumn('settings', 'account_center_blur_height')) {
                $table->integer('account_center_blur_height')->default(120);
            }
            if (!Schema::hasColumn('settings', 'account_center_blur_strength')) {
                $table->integer('account_center_blur_strength')->default(35);
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $columns = [
                'account_name_blur_enabled',
                'account_name_blur_x_offset_from_right',
                'account_name_blur_y',
                'account_name_blur_width',
                'account_name_blur_height',
                'account_name_blur_strength',
                'account_center_blur_enabled',
                'account_center_blur_x',
                'account_center_blur_y',
                'account_center_blur_width',
                'account_center_blur_height',
                'account_center_blur_strength',
            ];

            foreach ($columns as $col) {
                if (Schema::hasColumn('settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
