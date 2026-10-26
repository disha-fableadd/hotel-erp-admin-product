<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('attendances')) {
            return;
        }

        Schema::table('attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('attendances', 'check_in_latitude')) {
                $table->decimal('check_in_latitude', 10, 7)->nullable()->after('check_in_time');
            }
            if (! Schema::hasColumn('attendances', 'check_in_longitude')) {
                $table->decimal('check_in_longitude', 10, 7)->nullable()->after('check_in_latitude');
            }
            if (! Schema::hasColumn('attendances', 'check_in_location_name')) {
                $table->string('check_in_location_name', 255)->nullable()->after('check_in_longitude');
            }
            if (! Schema::hasColumn('attendances', 'check_in_ip_address')) {
                $table->string('check_in_ip_address', 45)->nullable()->after('check_in_location_name');
            }
            if (! Schema::hasColumn('attendances', 'check_out_latitude')) {
                $table->decimal('check_out_latitude', 10, 7)->nullable()->after('check_out_time');
            }
            if (! Schema::hasColumn('attendances', 'check_out_longitude')) {
                $table->decimal('check_out_longitude', 10, 7)->nullable()->after('check_out_latitude');
            }
            if (! Schema::hasColumn('attendances', 'check_out_location_name')) {
                $table->string('check_out_location_name', 255)->nullable()->after('check_out_longitude');
            }
            if (! Schema::hasColumn('attendances', 'check_out_ip_address')) {
                $table->string('check_out_ip_address', 45)->nullable()->after('check_out_location_name');
            }
            if (! Schema::hasColumn('attendances', 'meal_break')) {
                $table->time('meal_break')->nullable()->after('check_out_ip_address');
            }
            if (! Schema::hasColumn('attendances', 'overtime')) {
                $table->time('overtime')->nullable()->after('work_hours');
            }
            if (! Schema::hasColumn('attendances', 'checkin_method')) {
                $table->string('checkin_method', 50)->default('manual')->after('extraday');
            }
            if (! Schema::hasColumn('attendances', 'is_late')) {
                $table->tinyInteger('is_late')->default(0)->after('checkin_method');
            }
            if (! Schema::hasColumn('attendances', 'late_minutes')) {
                $table->integer('late_minutes')->default(0)->after('is_late');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('attendances')) {
            return;
        }

        Schema::table('attendances', function (Blueprint $table) {
            foreach ([
                'check_in_latitude',
                'check_in_longitude',
                'check_in_location_name',
                'check_in_ip_address',
                'check_out_latitude',
                'check_out_longitude',
                'check_out_location_name',
                'check_out_ip_address',
                'meal_break',
                'overtime',
                'checkin_method',
                'is_late',
                'late_minutes',
            ] as $column) {
                if (Schema::hasColumn('attendances', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
