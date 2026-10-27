<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed basic settings, role-wise users, master tables, and products.
     */
    public function run(): void
    {
        $this->call([
            BasicSettingsSeeder::class,
            RoleWiseUserSeeder::class,
            MasterDataSeeder::class,
           
        ]);
    }
}
