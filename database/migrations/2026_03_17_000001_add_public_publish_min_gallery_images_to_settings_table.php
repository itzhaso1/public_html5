<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'public_publish_min_gallery_images')) {
                $table->unsignedInteger('public_publish_min_gallery_images')->default(12);
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'public_publish_min_gallery_images')) {
                $table->dropColumn('public_publish_min_gallery_images');
            }
        });
    }
};

