<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->index('user_id'); // Add an index on user_id first so the foreign key has an index
            $table->dropUnique('attendances_user_id_date_unique'); // Now safe to drop
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->unique(['user_id', 'date'], 'attendances_user_id_date_unique');
        });
    }
};
