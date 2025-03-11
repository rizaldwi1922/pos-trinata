<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('factor', 10, 5)->default(1);
            $table->bigInteger('purchase_unit_id')->nullable()->unsigned();
            $table->foreign('purchase_unit_id')->references('id')->on('product_units')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropForeign(['purchase_unit_id']); // Hapus foreign key
            $table->dropColumn(['factor', 'purchase_unit_id']); // Hapus kolom yang ditambahkan
        });
    }
};
