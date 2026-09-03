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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'maba_color_ganjil')) {
                $table->string('maba_color_ganjil', 50)->nullable()->after('is_event_maba');
            }
            if (!Schema::hasColumn('products', 'maba_color_genap')) {
                $table->string('maba_color_genap', 50)->nullable()->after('maba_color_ganjil');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'maba_color_genap')) {
                $table->dropColumn('maba_color_genap');
            }
            if (Schema::hasColumn('products', 'maba_color_ganjil')) {
                $table->dropColumn('maba_color_ganjil');
            }
        });
    }
};
