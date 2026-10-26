<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Seed all application roles.
     */
    public function run(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        $roles = ['admin', 'sub-admin', 'staff', 'customer', 'vendor'];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }
    }
}
