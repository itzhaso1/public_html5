<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contactus_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_us_id')->constrained('contactus')->onDelete('cascade');
            $table->string('locale')->index();

            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('content_title')->nullable();
            $table->text('content_description')->nullable();
            $table->unique(['contact_us_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contactus_translations');
    }
};