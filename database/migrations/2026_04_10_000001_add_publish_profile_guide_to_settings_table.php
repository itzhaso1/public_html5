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
            if (!Schema::hasColumn('settings', 'public_publish_profile_guide_enabled')) {
                $table->boolean('public_publish_profile_guide_enabled')
                    ->default(false)
                    ->after('public_publish_min_gallery_images');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'public_publish_profile_guide_enabled')) {
                $table->dropColumn('public_publish_profile_guide_enabled');
            }
        });
    }
};

