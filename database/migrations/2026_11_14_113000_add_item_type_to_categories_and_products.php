<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('categories') && ! Schema::hasColumn('categories', 'item_type')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('item_type', 20)->default('food')->after('gst_applicable');
            });

            // Legacy Web rule: Apply GST OFF ≈ liquor share; GST ON ≈ food
            if (Schema::hasColumn('categories', 'gst_applicable')) {
                DB::table('categories')->where('gst_applicable', 0)->update(['item_type' => 'liquor']);
                DB::table('categories')->where(function ($q) {
                    $q->whereNull('gst_applicable')->orWhere('gst_applicable', 1);
                })->update(['item_type' => 'food']);
            }
        }

        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'item_type')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('item_type', 20)->nullable()->after('product_kind');
            });

            // Inherit from category when possible
            if (Schema::hasColumn('categories', 'item_type')) {
                $rows = DB::table('products')->whereNotNull('category_id')->select('id', 'category_id')->get();
                $catTypes = DB::table('categories')->pluck('item_type', 'id');
                foreach ($rows as $row) {
                    $type = $catTypes[$row->category_id] ?? null;
                    if ($type) {
                        DB::table('products')->where('id', $row->id)->update(['item_type' => $type]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'item_type')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('item_type');
            });
        }
        if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'item_type')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('item_type');
            });
        }
    }
};
