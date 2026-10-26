<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Notification;
use App\Models\LeaveTypeModel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Tests\TestCase;

class NotificationFeatureTest extends TestCase
{
    use DatabaseTransactions;

    protected function createBranch(int $branchId): void
    {
        DB::table('branches')->insertOrIgnore([
            'id' => $branchId,
            'name' => 'Test Branch ' . $branchId,
            'code' => 'BR' . str_pad((string) $branchId, 3, '0', STR_PAD_LEFT),
            'email' => 'branch' . $branchId . '@example.com',
            'phone' => '900000000' . $branchId,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function createStaffUser(int $branchId): User
    {
        return User::create([
            'name' => 'John Notification Tester',
            'email' => 'john_notif_' . $branchId . '@example.com',
            'phone' => '9876543' . str_pad((string) $branchId, 3, '0', STR_PAD_LEFT),
            'password' => bcrypt('password123'),
            'role' => 'hr',
            'branch_id' => $branchId,
            'isDeleted' => 0,
        ]);
    }

    public function test_login_check_in_creates_notification(): void
    {
        $branchId = 88;
        $this->createBranch($branchId);
        $staff = $this->createStaffUser($branchId);

        // Standard login check-in (no check-in should happen on login per requirements)
        $response = $this->postJson(route('login'), [
            'email' => $staff->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200);

        // Verify that no notification was created
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $staff->id,
            'type' => 'attendance',
            'title' => 'Staff Checked In',
            'branch_id' => $branchId,
        ]);
    }

    public function test_api_check_in_creates_notification(): void
    {
        $branchId = 89;
        $this->createBranch($branchId);
        $staff = $this->createStaffUser($branchId);
        Passport::actingAs($staff);

        $response = $this->postJson('/api/attendance/checkin', [
            'latitude' => 12.9716,
            'longitude' => 77.5946,
        ]);

        $response->assertStatus(200);

        // Verify notification
        $this->assertDatabaseHas('notifications', [
            'user_id' => $staff->id,
            'type' => 'attendance',
            'title' => 'Staff Checked In',
            'branch_id' => $branchId,
        ]);
    }

    public function test_api_checkout_creates_notification(): void
    {
        $branchId = 90;
        $this->createBranch($branchId);
        $staff = $this->createStaffUser($branchId);
        Passport::actingAs($staff);

        // Check in first
        $this->postJson('/api/attendance/checkin', [
            'latitude' => 12.9716,
            'longitude' => 77.5946,
        ])->assertStatus(200);

        // Check out
        $response = $this->postJson('/api/attendance/checkout');
        $response->assertStatus(200);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $staff->id,
            'type' => 'attendance',
            'title' => 'Staff Checked Out',
            'branch_id' => $branchId,
        ]);
    }

    public function test_leave_request_creates_notification(): void
    {
        $branchId = 91;
        $this->createBranch($branchId);
        $staff = $this->createStaffUser($branchId);
        Passport::actingAs($staff);

        $leaveType = LeaveTypeModel::create([
            'leave_type' => 'Casual Leave',
            'created_by' => $staff->id,
        ]);

        $response = $this->postJson('/api/leave', [
            'user_id' => $staff->id,
            'leave_id' => $leaveType->id,
            'reason' => 'Family function',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $staff->id,
            'type' => 'leave',
            'title' => 'New Leave Request',
            'branch_id' => $branchId,
        ]);
    }
}
