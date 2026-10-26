<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_licenses', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_code', 64)->unique();
            $table->string('purchase_code_hash', 64)->index();
            $table->string('buyer', 120);
            $table->enum('license_type', ['regular', 'extended', 'development'])->default('regular');
            $table->unsignedTinyInteger('max_domains')->default(1);
            $table->enum('status', ['active', 'revoked', 'suspended'])->default('active');
            $table->string('envato_item_id', 64)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('product_license_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_license_id')->constrained('product_licenses')->cascadeOnDelete();
            $table->string('domain', 253);
            $table->string('installation_id', 64);
            $table->timestamp('activated_at')->useCurrent();
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();

            $table->unique(['product_license_id', 'domain'], 'pld_license_domain_unique');
            $table->unique(['product_license_id', 'installation_id'], 'pld_license_install_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_license_domains');
        Schema::dropIfExists('product_licenses');
    }
};

