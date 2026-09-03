<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('product_reviews', 'is_read')) {
            Schema::table('product_reviews', function (Blueprint $table) {
                $table->boolean('is_read')->default(false)->after('reply');
            });

            // Mark reviews as read if they already have replies or admin replies
            DB::table('product_reviews')
                ->whereNotNull('reply')
                ->where('reply', '!=', '')
                ->update(['is_read' => true]);

            DB::table('product_reviews')
                ->whereIn('id', function($query) {
                    $query->select('product_review_id')->from('product_review_replies');
                })
                ->update(['is_read' => true]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('product_reviews', 'is_read')) {
            Schema::table('product_reviews', function (Blueprint $table) {
                $table->dropColumn('is_read');
            });
        }
    }
};
