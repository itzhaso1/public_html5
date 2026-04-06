<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('setting_watermarks')) {
            Schema::create('setting_watermarks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('setting_id')->index();
                $table->string('title')->nullable();
                $table->boolean('enabled')->default(true);
                $table->integer('sort_order')->default(0)->index();
                $table->integer('x_offset')->default(20);
                $table->integer('y_offset')->default(0);
                $table->integer('scale_percent')->default(20);
                $table->string('collection_name')->nullable();
                $table->timestamps();

                $table->foreign('setting_id')
                    ->references('id')
                    ->on('settings')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('setting_watermarks')) {
            Schema::dropIfExists('setting_watermarks');
        }
    }
};
