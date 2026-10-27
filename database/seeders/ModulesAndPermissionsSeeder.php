<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Plan;
use App\Services\PlanPermissionSyncService;

class ModulesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Disable foreign keys, truncate to ensure a clean slate, and set SQL mode to allow zero IDs
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('user_permissions')->truncate();
        DB::table('modules')->truncate();
        DB::statement("SET sql_mode='NO_AUTO_VALUE_ON_ZERO';");
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Check if an active plan exists
        $plan = Plan::where('is_active', 1)->latest('id')->first()
            ?: Plan::latest('id')->first();

        // Synchronize modules and user_permissions strictly according to the plan
        PlanPermissionSyncService::syncPlanModulesAndPermissions($plan);
    }
}
