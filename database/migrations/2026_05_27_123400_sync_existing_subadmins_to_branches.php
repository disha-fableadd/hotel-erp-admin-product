<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('users') && Schema::hasTable('branches')) {
            $subadmins = DB::table('users')->where('role', 'sub-admin')->get();
            foreach ($subadmins as $subadmin) {
                // Ensure a matching branch exists
                DB::table('branches')->updateOrInsert(
                    ['id' => $subadmin->id],
                    [
                        'name' => $subadmin->name,
                        'code' => 'BR' . str_pad($subadmin->id, 3, '0', STR_PAD_LEFT),
                        'email' => $subadmin->email,
                        'phone' => $subadmin->phone,
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                // Set user's branch_id to their id
                DB::table('users')->where('id', $subadmin->id)->update([
                    'branch_id' => $subadmin->id
                ]);
            }
        }
    }

    public function down(): void
    {
        // No rollback needed for data sync
    }
};
