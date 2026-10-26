<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Module;
use App\Models\UserPermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use Tests\TestCase;

class StaffControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user first to get their auto-generated ID
        $this->admin = User::create([
            'name' => 'Admin Test User',
            'email' => 'admin_test@example.com',
            'phone' => '1234567890',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'isDeleted' => 0,
        ]);

        // Create a test branch matching the admin's ID to satisfy the branch_id foreign key constraint
        \Illuminate\Support\Facades\DB::table('branches')->insertOrIgnore([
            'id' => $this->admin->id,
            'name' => 'Test Branch',
            'code' => 'BR' . str_pad($this->admin->id, 3, '0', STR_PAD_LEFT),
            'email' => 'branch@example.com',
            'phone' => '1234567890',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Set the admin's branch_id to their own ID
        $this->admin->branch_id = $this->admin->id;
        $this->admin->save();
    }

    public function test_create_staff_creates_default_permissions_and_user_details()
    {
        Passport::actingAs($this->admin);

        // Make sure modules exist
        $attendance = Module::firstOrCreate(['module' => 'Attendance']);
        $leaves = Module::firstOrCreate(['module' => 'Leaves']);
        $payroll = Module::firstOrCreate(['module' => 'Payroll']);

        $payload = [
            'customer_name' => 'John Staff',
            'email' => 'john_staff@example.com',
            'role' => 'staff',
            'phone' => '9876543210',
            'password' => 'password123',
            'country' => 'India',
            'city' => 'Delhi',
            'address' => '123 Street',
            'joining_date' => '2026-06-10',
            'salary' => 5000,
            'permission_type' => 1, // With permissions
            'modules' => [
                [
                    'module_id' => $attendance->id,
                    'view' => 1,
                    'add' => 1,
                ]
            ]
        ];

        $response = $this->postJson('/api/createStaff', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'status' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Staff',
            'email' => 'john_staff@example.com',
            'role' => 'staff',
        ]);

        $newStaff = User::where('email', 'john_staff@example.com')->first();

        $this->assertDatabaseHas('user_details', [
            'user_id' => $newStaff->id,
            'country' => 'India',
            'city' => 'Delhi',
        ]);

        // Attendance (default) & Leaves (default) should be created
        $this->assertDatabaseHas('user_permissions', [
            'user_id' => $newStaff->id,
            'module_id' => $attendance->id,
            'view' => 1,
            'add' => 1,
        ]);

        $this->assertDatabaseHas('user_permissions', [
            'user_id' => $newStaff->id,
            'module_id' => $leaves->id,
            'view' => 1,
            'add' => 1,
        ]);
    }

    public function test_update_staff_saves_permissions_without_constraint_violation()
    {
        Passport::actingAs($this->admin);

        // Create a staff user first
        $staff = User::create([
            'name' => 'Jane Staff',
            'email' => 'jane_staff@example.com',
            'phone' => '8888888888',
            'password' => bcrypt('password123'),
            'role' => 'staff',
            'branch_id' => 1,
            'isDeleted' => 0,
        ]);

        // Ensure modules exist
        $attendance = Module::firstOrCreate(['module' => 'Attendance']);
        $leaves = Module::firstOrCreate(['module' => 'Leaves']);

        $payload = [
            'staff_name' => 'Jane Staff Updated',
            'email' => 'jane_staff_updated@example.com',
            'phone' => '8888888888',
            'role' => 'staff',
            'joining_date' => '2026-06-10',
            'salary' => 6000,
            'permission_type' => 0, // Without permissions (should only have default ones)
        ];

        $response = $this->postJson("/api/updateStaff/{$staff->id}", $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'name' => 'Jane Staff Updated',
            'email' => 'jane_staff_updated@example.com',
        ]);

        // Confirm default permissions are populated for Attendance and Leaves
        $this->assertDatabaseHas('user_permissions', [
            'user_id' => $staff->id,
            'module_id' => $attendance->id,
            'view' => 1,
        ]);

        $this->assertDatabaseHas('user_permissions', [
            'user_id' => $staff->id,
            'module_id' => $leaves->id,
            'view' => 1,
        ]);
    }

    public function test_create_staff_duplicate_email_validation()
    {
        Passport::actingAs($this->admin);

        // Create existing user
        User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'phone' => '7777777777',
            'password' => bcrypt('password123'),
            'role' => 'staff',
            'branch_id' => 1,
            'isDeleted' => 0,
        ]);

        $payload = [
            'customer_name' => 'John Duplicate',
            'email' => 'existing@example.com', // Duplicate
            'role' => 'staff',
            'phone' => '9999999999',
            'password' => 'password123',
            'joining_date' => '2026-06-10',
            'salary' => 5000,
            'permission_type' => 0,
        ];

        $response = $this->postJson('/api/createStaff', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        $this->assertEquals(
            'Already exist with this mail',
            $response['errors']['email'][0]
        );
    }
}
