<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

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

        $modules = [
            ['id' => 0, 'module' => 'Dashboard'],
            ['id' => 1, 'module' => 'Products'],
            ['id' => 2, 'module' => 'Orders'],
            ['id' => 3, 'module' => 'Purchases'],
            ['id' => 4, 'module' => 'Invoices'],
            ['id' => 5, 'module' => 'Expenses'],
            ['id' => 8, 'module' => 'Categories & Brands'],
            ['id' => 9, 'module' => 'Tables'],
            ['id' => 10, 'module' => 'Vendors'],
            ['id' => 11, 'module' => 'Staff'],
            ['id' => 14, 'module' => 'General Settings'],
            ['id' => 15, 'module' => 'Tax Rates'],
            ['id' => 16, 'module' => 'Accounting'],
            ['id' => 17, 'module' => 'Inventory'],
            ['id' => 23, 'module' => 'Advance Pay'],
            ['id' => 26, 'module' => 'Attendance'],
            ['id' => 27, 'module' => 'Cash & Bank'],
            ['id' => 28, 'module' => 'Leaves'],
            ['id' => 29, 'module' => 'Payroll'],
            ['id' => 30, 'module' => 'Credit/Debit Notes'],
        ];

        DB::table('modules')->insert($modules);

        // 3. Seed full permissions for admin, sub-admin, and staff users
        $users = User::whereIn('role', ['admin', 'sub-admin', 'staff'])->get();

        foreach ($users as $user) {
            if ($user->role === 'sub-admin' || $user->role === 'staff') {
                $user->haspermission = 1;
                $user->save();
            }

            foreach ($modules as $module) {
                DB::table('user_permissions')->updateOrInsert(
                    [
                        'user_id' => $user->id,
                        'module_id' => $module['id']
                    ],
                    [
                        'view' => true,
                        'add' => true,
                        'edit' => true,
                        'delete' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
