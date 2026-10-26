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
        Schema::table('leaves', function (Blueprint $table) {
            $table->decimal('no_of_day', 8, 2)->change();
            if (!Schema::hasColumn('leaves', 'duration')) {
                $table->string('duration', 50)->default('Full Day')->after('no_of_day');
            }
            if (!Schema::hasColumn('leaves', 'half_day_type')) {
                $table->string('half_day_type', 50)->nullable()->after('duration');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->integer('no_of_day')->change();
            $table->dropColumn(['duration', 'half_day_type']);
        });
    }
};
