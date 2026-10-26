<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StaffAttendanceRestrictionTest extends TestCase
{
    use DatabaseTransactions;

    protected function createStaffUser(): User
    {
        $rand = rand(100000, 999999);
        return User::create([
            'name' => 'Staff Member',
            'email' => 'staff_restrict_' . $rand . '@example.com',
            'phone' => '1' . $rand . '123',
            'password' => bcrypt('password123'),
            'role' => 'staff',
            'branch_id' => 1,
            'isDeleted' => 0,
        ]);
    }

    /**
     * Verify staff user CAN check in via the web header button.
     */
    public function test_staff_can_check_in_via_web(): void
    {
        $staff = $this->createStaffUser();
        $this->actingAs($staff, 'web');

        $response = $this->postJson(route('staff.checkin'), [
            'check_in_latitude'  => 12.9715987,
            'check_in_longitude' => 77.5945627,
        ]);

        // Staff are now allowed to check in via the web header buttons
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }

    /**
     * Verify staff user CAN check out via the web header button.
     * (Requires an open check-in record first.)
     */
    public function test_staff_can_check_out_via_web(): void
    {
        $staff = $this->createStaffUser();
        $this->actingAs($staff, 'web');

        // Create a check-in record so check-out has something to close
        Attendance::create([
            'user_id'       => $staff->id,
            'branch_id'     => $staff->branch_id ?? 0,
            'date'          => now('Asia/Kolkata')->toDateString(),
            'check_in_time' => now('Asia/Kolkata')->subHours(2)->format('H:i:s'),
            'status'        => 'present',
        ]);

        $response = $this->postJson(route('staff.checkout'), [
            'check_out_latitude'  => 12.9715987,
            'check_out_longitude' => 77.5945627,
        ]);

        // Staff are now allowed to check out via the web header buttons
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }

    /**
     * Verify staff user cannot delete attendance.
     */
    public function test_staff_cannot_delete_attendance(): void
    {
        $staff = $this->createStaffUser();
        $this->actingAs($staff, 'api');

        $response = $this->deleteJson('/api/attendance/delete-today');

        $response->assertStatus(403);
    }

    /**
     * Verify staff user cannot store/save attendance via API.
     */
    public function test_staff_cannot_store_attendance_via_api(): void
    {
        $staff = $this->createStaffUser();
        $this->actingAs($staff, 'api');

        $response = $this->postJson(route('attendance.store'), [
            'user_id' => $staff->id,
            'date' => now()->toDateString(),
            'status' => 'P',
            'extraday' => 0,
        ]);

        $response->assertStatus(403);
    }

    protected function createHRUser(): User
    {
        $rand = rand(100000, 999999);
        return User::create([
            'name' => 'HR Member',
            'email' => 'hr_restrict_' . $rand . '@example.com',
            'phone' => '2' . $rand . '123',
            'password' => bcrypt('password123'),
            'role' => 'hr',
            'branch_id' => 1,
            'isDeleted' => 0,
        ]);
    }

    /**
     * Verify hr user CAN check in via the web header button.
     */
    public function test_hr_can_check_in_via_web(): void
    {
        $hr = $this->createHRUser();
        $this->actingAs($hr, 'web');

        $response = $this->postJson(route('staff.checkin'), [
            'check_in_latitude'  => 12.9715987,
            'check_in_longitude' => 77.5945627,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }

    /**
     * Verify hr user CAN check out via the web header button.
     */
    public function test_hr_can_check_out_via_web(): void
    {
        $hr = $this->createHRUser();
        $this->actingAs($hr, 'web');

        // Create a check-in record so check-out has something to close
        Attendance::create([
            'user_id'       => $hr->id,
            'branch_id'     => $hr->branch_id ?? 0,
            'date'          => now('Asia/Kolkata')->toDateString(),
            'check_in_time' => now('Asia/Kolkata')->subHours(2)->format('H:i:s'),
            'status'        => 'present',
        ]);

        $response = $this->postJson(route('staff.checkout'), [
            'check_out_latitude'  => 12.9715987,
            'check_out_longitude' => 77.5945627,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }
}

