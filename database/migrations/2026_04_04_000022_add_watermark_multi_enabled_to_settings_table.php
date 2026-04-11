<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('settings', 'watermark_multi_enabled')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->boolean('watermark_multi_enabled')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('settings', 'watermark_multi_enabled')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('watermark_multi_enabled');
            });
        }
    }
};
