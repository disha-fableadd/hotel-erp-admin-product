<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_details')) {
            return;
        }

        Schema::table('user_details', function (Blueprint $table) {
            if (!Schema::hasColumn('user_details', 'designation_id')) {
                $table->unsignedBigInteger('designation_id')->nullable()->after('user_id');
            }

            if (!Schema::hasColumn('user_details', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->after('designation_id');
            }

            if (!Schema::hasColumn('user_details', 'joining_date')) {
                $table->date('joining_date')->nullable()->after('country');
            }

            if (!Schema::hasColumn('user_details', 'working_location')) {
                $table->string('working_location')->nullable()->after('joining_date');
            }

            if (!Schema::hasColumn('user_details', 'shift_time')) {
                $table->string('shift_time')->nullable()->after('working_location');
            }

            if (!Schema::hasColumn('user_details', 'salary')) {
                $table->decimal('salary', 12, 2)->nullable()->after('shift_time');
            }

            if (!Schema::hasColumn('user_details', 'face_photo')) {
                $table->string('face_photo')->nullable()->after('salary');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('user_details')) {
            return;
        }

        Schema::table('user_details', function (Blueprint $table) {
            $columns = [];
            foreach (['designation_id', 'department_id', 'joining_date', 'working_location', 'shift_time', 'salary', 'face_photo'] as $col) {
                if (Schema::hasColumn('user_details', $col)) {
                    $columns[] = $col;
                }
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
