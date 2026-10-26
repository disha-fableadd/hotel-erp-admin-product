<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\LocationSettingsModel;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FaceLoginFeatureTest extends TestCase
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

    protected function createStaffUser(int $branchId, array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'John Doe Feature Test',
            'email' => 'john_feature_test_' . $branchId . '@example.com',
            'phone' => '9876543' . str_pad((string) $branchId, 3, '0', STR_PAD_LEFT),
            'password' => bcrypt('password123'),
            'role' => 'staff',
            'branch_id' => $branchId,
            'isDeleted' => 0,
        ], $overrides));
    }

    protected function createBranchSettings(int $branchId, bool $locationCheckEnabled = false): Setting
    {
        return Setting::create([
            'branch_id' => $branchId,
            'name' => 'Test Branch Settings ' . $branchId,
            'email' => 'settings' . $branchId . '@example.com',
            'phone' => '9999999' . $branchId,
            'address' => 'Test Address',
            'currency_position' => 'before',
            'currency_symbol' => '₹',
            'low_stock' => 10,
            'location_check_on_login' => $locationCheckEnabled ? 1 : 0,
        ]);
    }

    protected function createOfficeLocation(float $latitude, float $longitude, int $radius = 500): LocationSettingsModel
    {
        return LocationSettingsModel::create([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'radius' => $radius,
        ]);
    }

    /**
     * Test successful face login for staff member.
     */
    public function test_staff_face_login_success(): void
    {
        // 1. Create a dummy staff user with a face descriptor
        $descriptor = array_fill(0, 128, 0.1);

        $staff = $this->createStaffUser(1, [
            'phone' => '9876543210',
            'face_descriptor' => $descriptor,
        ]);

        // 2. Post correct descriptor to face-login endpoint
        $response = $this->postJson(route('auth.face-login'), [
            'face_descriptor' => $descriptor,
        ]);

        // 3. Assert successful login
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'token',
            'user',
            'redirect',
            'login_method',
        ]);
        
        $this->assertTrue($response['status']);
        $this->assertEquals('face', $response['login_method']);
        $this->assertEquals(route('auth.staff-dashboard'), $response['redirect']);
        
        // Assert that the user is authenticated in the web guard
        $this->assertAuthenticatedAs($staff, 'web');

        // Assert no attendance record is created for face login
        $this->assertDatabaseMissing('attendances', [
            'user_id' => $staff->id,
        ]);
    }

    public function test_staff_face_login_requires_gps_when_company_rule_enabled(): void
    {
        $this->markTestSkipped('Location check bypassed for face login as requested.');
        $descriptor = array_fill(0, 128, 0.1);
        $branchId = 21;

        $this->createBranch($branchId);
        $this->createBranchSettings($branchId, true);
        $this->createOfficeLocation(12.9715987, 77.5945627, 250);

        $staff = $this->createStaffUser($branchId, [
            'face_descriptor' => $descriptor,
        ]);

        $response = $this->postJson(route('auth.face-login'), [
            'face_descriptor' => $descriptor,
            'latitude' => 28.7041,
            'longitude' => 77.1025,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'status' => false,
            'error' => 'You must be within the configured office radius to check in.',
        ]);

        $this->assertDatabaseMissing('attendances', [
            'user_id' => $staff->id,
        ]);
    }

    public function test_staff_face_login_succeeds_when_location_rule_is_enabled_and_coordinates_are_inside_radius(): void
    {
        $this->markTestSkipped('Location check bypassed for face login as requested.');
        $descriptor = array_fill(0, 128, 0.1);
        $branchId = 22;
        $officeLat = 12.9715987;
        $officeLng = 77.5945627;

        $this->createBranch($branchId);
        $this->createBranchSettings($branchId, true);
        $this->createOfficeLocation($officeLat, $officeLng, 300);

        $staff = $this->createStaffUser($branchId, [
            'face_descriptor' => $descriptor,
        ]);

        $response = $this->postJson(route('auth.face-login'), [
            'face_descriptor' => $descriptor,
            'latitude' => $officeLat + 0.0001,
            'longitude' => $officeLng + 0.0001,
            'location_name' => 'Front Office',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'login_method' => 'face',
            'redirect' => route('auth.staff-dashboard'),
        ]);

        $this->assertAuthenticatedAs($staff, 'web');
        $this->assertDatabaseHas('attendances', [
            'user_id' => $staff->id,
            'branch_id' => $branchId,
            'status' => 'present',
            'checkin_method' => 'face_recognition',
        ]);
    }

    public function test_staff_face_login_does_not_block_when_already_checked_in(): void
    {
        $descriptor = array_fill(0, 128, 0.1);
        $branchId = 23;

        $this->createBranch($branchId);
        $this->createBranchSettings($branchId, true);

        $staff = $this->createStaffUser($branchId, [
            'face_descriptor' => $descriptor,
        ]);

        // Create an existing attendance record for today (already checked in)
        Attendance::create([
            'user_id' => $staff->id,
            'branch_id' => $branchId,
            'date' => now('Asia/Kolkata')->format('Y-m-d'),
            'check_in_time' => '09:00:00',
            'status' => 'present',
        ]);

        $payload = [
            'face_descriptor' => $descriptor,
        ];

        // First face login should succeed and redirect
        $first = $this->postJson(route('auth.face-login'), $payload);
        $first->assertStatus(200);
        $first->assertJson([
            'status' => true,
            'login_method' => 'face',
            'redirect' => route('auth.staff-dashboard'),
        ]);

        // Second face login should also succeed (no duplicate check-in blocking)
        $second = $this->postJson(route('auth.face-login'), $payload);
        $second->assertStatus(200);
        $second->assertJson([
            'status' => true,
            'login_method' => 'face',
            'redirect' => route('auth.staff-dashboard'),
        ]);
    }

    /**
     * Test face login failure for unrecognized faces.
     */
    public function test_staff_face_login_no_match(): void
    {
        // Post a descriptor that doesn't match any staff member
        $response = $this->postJson(route('auth.face-login'), [
            'face_descriptor' => array_fill(0, 128, 0.9), // far descriptor
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'status' => false,
            'error' => 'Face not recognized. Please try again or use password.',
        ]);
    }

    /**
     * Test successful standard email/password login for staff member.
     * Asserts it does not record check-in/attendance and redirects to staff dashboard.
     */
    public function test_staff_standard_login_success_without_check_in(): void
    {
        $branchId = 24;
        $this->createBranch($branchId);

        $staff = $this->createStaffUser($branchId, [
            'email' => 'john_std_login@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson(route('login'), [
            'email' => 'john_std_login@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'token',
            'user',
            'redirect',
        ]);

        $this->assertTrue($response['status']);
        $this->assertEquals(route('auth.staff-dashboard'), $response['redirect']);

        // Assert that the user is authenticated in the web guard
        $this->assertAuthenticatedAs($staff, 'web');

        // Assert no attendance record is created
        $this->assertDatabaseMissing('attendances', [
            'user_id' => $staff->id,
        ]);
    }

    /**
     * Test successful standard login for staff with capitalized role name (e.g. 'Staff').
     * Verifies case-insensitivity of staff role validation.
     */
    public function test_staff_standard_login_case_insensitive_role(): void
    {
        $branchId = 25;
        $this->createBranch($branchId);

        $staff = $this->createStaffUser($branchId, [
            'email' => 'john_capital_role@example.com',
            'password' => bcrypt('password123'),
            'role' => 'Staff', // Capitalized 'S'
        ]);

        $response = $this->postJson(route('login'), [
            'email' => 'john_capital_role@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response['status']);
        $this->assertEquals(route('auth.staff-dashboard'), $response['redirect']);
        
        $this->assertAuthenticatedAs($staff, 'web');
    }
}
