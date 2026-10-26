<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class RoleWiseUserSeeder extends Seeder
{
    /**
     * Seed users for each application role: admin, sub-admin, staff, customer, vendor.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('12345678');

        // Look up default staff department and designation if available
        $staffDeptId = DB::table('department')->where('department_name', 'F&B Service')->value('id')
            ?? DB::table('department')->value('id');

        $staffDesigId = null;
        if ($staffDeptId) {
            $staffDesigId = DB::table('designation')->where('department_id', $staffDeptId)->value('id')
                ?? DB::table('designation')->value('id');
        }

        $usersData = [
            [
                'email' => 'admin@gmail.com',
                'name' => 'Admin',
                'phone' => '1234567890',
                'role' => 'admin',
                'haspermission' => 1,
                'city' => 'Surat',
                'address' => '5001, Ascon Plaza , Adjana Surat',
                'department_id' => null,
                'designation_id' => null,
                'salary' => null,
            ],
            [
                'email' => 'subadmin@gmail.com',
                'name' => 'Sub Admin',
                'phone' => '1234567892',
                'role' => 'sub-admin',
                'haspermission' => 1,
                'city' => 'Surat',
                'address' => 'Adajan, Surat',
                'department_id' => null,
                'designation_id' => null,
                'salary' => null,
            ],
            [
                'email' => 'staff@gmail.com',
                'name' => 'Staff User',
                'phone' => '1234567893',
                'role' => 'staff',
                'haspermission' => 1,
                'city' => 'Surat',
                'address' => 'Adajan, Surat',
                'department_id' => $staffDeptId,
                'designation_id' => $staffDesigId,
                'salary' => 25000.00,
            ],
            [
                'email' => 'customer@gmail.com',
                'name' => 'Default Customer',
                'phone' => '1234567891',
                'role' => 'customer',
                'haspermission' => 0,
                'city' => 'Surat',
                'address' => 'Default Address',
                'department_id' => null,
                'designation_id' => null,
                'salary' => null,
            ],
            [
                'email' => 'vendor@gmail.com',
                'name' => 'Default Vendor',
                'phone' => '1234567894',
                'role' => 'vendor',
                'haspermission' => 0,
                'city' => 'Surat',
                'address' => 'Default Address',
                'department_id' => null,
                'designation_id' => null,
                'salary' => null,
            ],
        ];

        foreach ($usersData as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'password' => $defaultPassword,
                    'role' => $data['role'],
                    'status' => 1,
                    'isDeleted' => 0,
                    'branch_id' => 1,
                    'haspermission' => $data['haspermission'],
                ]
            );

            // User Details
            if (Schema::hasTable('user_details')) {
                $detailPayload = [
                    'address' => $data['address'],
                    'city' => $data['city'],
                    'country' => 'India',
                    'isDeleted' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (!empty($data['department_id'])) {
                    $detailPayload['department_id'] = $data['department_id'];
                }
                if (!empty($data['designation_id'])) {
                    $detailPayload['designation_id'] = $data['designation_id'];
                }
                if (!empty($data['salary'])) {
                    $detailPayload['salary'] = $data['salary'];
                    $detailPayload['joining_date'] = now()->toDateString();
                }

                DB::table('user_details')->updateOrInsert(
                    ['user_id' => $user->id],
                    $detailPayload
                );
            }

            // Spatie Role Assignment
            if (Schema::hasTable('roles')) {
                $role = Role::where('name', $data['role'])->where('guard_name', 'web')->first();
                if ($role) {
                    if (method_exists($user, 'assignRole')) {
                        $user->syncRoles([$data['role']]);
                    } elseif (Schema::hasTable('model_has_roles')) {
                        DB::table('model_has_roles')->updateOrInsert(
                            [
                                'role_id' => $role->id,
                                'model_type' => User::class,
                                'model_id' => $user->id,
                            ],
                            []
                        );
                    }
                }
            }
        }
    }
}
