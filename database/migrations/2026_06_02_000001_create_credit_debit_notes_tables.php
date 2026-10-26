<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the three tables required for Credit/Debit Notes:
     *   - credit_notes_type
     *   - credit_note_items
     *   - debit_notes_type
     */
    public function up(): void
    {
        // ── credit_notes_type ────────────────────────────────────────────────
        Schema::create('credit_notes_type', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('type_name');
            $table->tinyInteger('isdeleted')->default(0);
            $table->timestamps();
        });

        // ── credit_note_items ────────────────────────────────────────────────
        Schema::create('credit_note_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('credite_note_id')->nullable();
            $table->unsignedBigInteger('type_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('purchase_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->decimal('total_amt', 12, 2)->default(0.00);
            $table->decimal('remaining_amt', 12, 2)->default(0.00);
            $table->decimal('total_paid', 12, 2)->default(0.00);
            $table->decimal('settlement_amount', 12, 2)->default(0.00);
            $table->text('reason')->nullable();
            $table->decimal('total', 12, 2)->default(0.00);
            $table->tinyInteger('isDeleted')->default(0);
            $table->timestamps();

            $table->index('credite_note_id', 'credit_note_items_credite_note_id_foreign');
            $table->index('user_id', 'credit_note_items_user_id_foreign');
        });

        // ── debit_notes_type ─────────────────────────────────────────────────
        Schema::create('debit_notes_type', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('transaction_type')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('purchase_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('create_note_id')->nullable();
            $table->string('invoice_number')->nullable();
            $table->decimal('grand_total', 12, 2)->default(0.00);
            $table->decimal('remaning_amount', 12, 2)->default(0.00);
            $table->decimal('total_paid', 12, 2)->default(0.00);
            $table->decimal('settlement_amount', 12, 2)->default(0.00);
            $table->text('reason')->nullable();
            $table->decimal('total', 12, 2)->default(0.00);
            $table->tinyInteger('isDeleted')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debit_notes_type');
        Schema::dropIfExists('credit_note_items');
        Schema::dropIfExists('credit_notes_type');
    }
};
