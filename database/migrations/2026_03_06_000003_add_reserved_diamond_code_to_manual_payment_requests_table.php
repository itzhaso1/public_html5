<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manual_payment_requests', function (Blueprint $table) {
            $table->foreignId('reserved_diamond_code_id')
                ->nullable()
                ->after('product_id')
                ->constrained('diamond_codes')
                ->nullOnDelete();

            $table->index(['product_id', 'status', 'reserved_diamond_code_id'], 'mpr_reserved_lookup');
        });
    }

    public function down(): void
    {
        Schema::table('manual_payment_requests', function (Blueprint $table) {
            $table->dropIndex('mpr_reserved_lookup');
            $table->dropConstrainedForeignId('reserved_diamond_code_id');
        });
    }
};

