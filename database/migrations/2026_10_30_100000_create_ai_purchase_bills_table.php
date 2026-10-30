<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_purchase_bills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->index();
            $table->unsignedBigInteger('created_by');
            $table->uuid('draft_token')->unique();
            $table->string('file_path');
            $table->string('original_name');
            $table->json('extraction');
            $table->json('reviewed_data')->nullable();
            $table->unsignedBigInteger('purchase_invoice_id')->nullable()->index();
            $table->timestamps();
        });
        // Hotel stock frequently uses fractional kg/litres. Integer columns
        // silently lose these quantities in both the catalog and purchase log.
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('quantity', 14, 4)->nullable()->default(0)->change();
        });
        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('quantity', 14, 4)->default(1)->change();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_purchase_bills');
        // Retain decimal precision: reverting to integers would destroy stock data.
    }
};
