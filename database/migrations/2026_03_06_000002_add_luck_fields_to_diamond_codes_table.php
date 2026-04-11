<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('diamond_codes', function (Blueprint $table) {
            $table->unsignedInteger('luck_weight')
                ->default(1)
                ->after('image_path')
                ->index();
            $table->string('luck_label')
                ->nullable()
                ->after('luck_weight');
        });
    }

    public function down(): void
    {
        Schema::table('diamond_codes', function (Blueprint $table) {
            $table->dropIndex(['luck_weight']);
            $table->dropColumn(['luck_weight', 'luck_label']);
        });
    }
};

