<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('settings', 'account_center_blur_x_from_right')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->integer('account_center_blur_x_from_right')->default(0)->after('account_center_blur_x');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('settings', 'account_center_blur_x_from_right')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('account_center_blur_x_from_right');
            });
        }
    }
};
