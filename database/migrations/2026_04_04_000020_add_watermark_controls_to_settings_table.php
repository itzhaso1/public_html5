<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'watermark_enabled')) {
                $table->boolean('watermark_enabled')->default(true);
            }
            if (!Schema::hasColumn('settings', 'watermark_x_offset')) {
                $table->integer('watermark_x_offset')->default(20);
            }
            if (!Schema::hasColumn('settings', 'watermark_y_offset')) {
                $table->integer('watermark_y_offset')->default(0);
            }
            if (!Schema::hasColumn('settings', 'watermark_scale_percent')) {
                $table->integer('watermark_scale_percent')->default(20);
            }
            if (!Schema::hasColumn('settings', 'watermark_second_enabled')) {
                $table->boolean('watermark_second_enabled')->default(true);
            }
            if (!Schema::hasColumn('settings', 'watermark_second_x_offset')) {
                $table->integer('watermark_second_x_offset')->default(40);
            }
            if (!Schema::hasColumn('settings', 'watermark_second_y_offset')) {
                $table->integer('watermark_second_y_offset')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $cols = [
                'watermark_enabled',
                'watermark_x_offset',
                'watermark_y_offset',
                'watermark_scale_percent',
                'watermark_second_enabled',
                'watermark_second_x_offset',
                'watermark_second_y_offset',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

