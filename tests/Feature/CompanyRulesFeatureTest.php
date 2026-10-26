<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CompanyRulesFeatureTest extends TestCase
{
    use DatabaseTransactions;

    protected function createBranchAndAdmin(int $branchId): User
    {
        DB::table('branches')->insertOrIgnore([
            'id' => $branchId,
            'name' => 'Test Branch ' . $branchId,
            'code' => 'BR' . str_pad((string) $branchId, 3, '0', STR_PAD_LEFT),
            'email' => 'branch' . $branchId . '@example.com',
            'phone' => '9000000' . $branchId,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insertOrIgnore([
            'id' => $branchId,
            'name' => 'Admin ' . $branchId,
            'email' => 'admin' . $branchId . '@example.com',
            'phone' => '8888888' . $branchId,
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'branch_id' => $branchId,
            'isDeleted' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::find($branchId);
    }

    public function test_company_rules_update_persists_location_check_toggle(): void
    {
        $admin = $this->createBranchAndAdmin(31);
        Passport::actingAs($admin);

        Setting::create([
            'branch_id' => $admin->id,
            'name' => 'Company Rules Branch',
            'email' => 'settings31@example.com',
            'phone' => '777777731',
            'address' => 'Main Office',
            'currency_position' => 'before',
            'currency_symbol' => '₹',
            'low_stock' => 10,
            'working_hours' => 8,
            'sunday_off' => 'no',
            'grace_period' => 10,
            'lunch_break' => 30,
            'open_time' => '09:00',
            'close_time' => '18:00',
            'location_check_on_login' => 0,
        ]);

        $response = $this->postJson('/api/company-rules/update', [
            'selectedSubAdminId' => $admin->id,
            'working_hours' => 8,
            'sunday_off' => 'no',
            'grace_period' => 15,
            'lunch_break' => 30,
            'open_time' => '09:00',
            'close_time' => '18:00',
            'location_check_on_login' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Company rules updated successfully',
        ]);

        $this->assertDatabaseHas('settings', [
            'branch_id' => $admin->id,
            'location_check_on_login' => 1,
            'grace_period' => 15,
            'open_time' => '09:00:00',
            'close_time' => '18:00:00',
        ]);
    }

    public function test_dashboard_settings_update_persists_hr_visibility_toggle(): void
    {
        $admin = $this->createBranchAndAdmin(32);
        Passport::actingAs($admin);

        Setting::create([
            'branch_id' => $admin->id,
            'name' => 'Dashboard Settings Branch',
            'email' => 'settings32@example.com',
            'phone' => '777777732',
            'address' => 'Main Office',
            'currency_position' => 'before',
            'currency_symbol' => '₹',
            'low_stock' => 10,
            'show_hr_dashboard' => 1,
        ]);

        $response = $this->postJson('/api/dashboard-settings/update', [
            'selectedSubAdminId' => $admin->id,
            'show_hr_dashboard' => 0,
            'show_hr_staff_strength' => 0,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Dashboard settings updated successfully',
        ]);

        $this->assertDatabaseHas('settings', [
            'branch_id' => $admin->id,
            'show_hr_dashboard' => 0,
            'show_hr_staff_strength' => 0,
        ]);
    }

    public function test_company_rules_update_handles_null_string_selectedSubAdminId(): void
    {
        $admin = $this->createBranchAndAdmin(33);
        Passport::actingAs($admin);

        Setting::create([
            'branch_id' => $admin->id,
            'name' => 'Company Rules Branch 33',
            'email' => 'settings33@example.com',
            'phone' => '777777733',
            'address' => 'Main Office',
            'currency_position' => 'before',
            'currency_symbol' => '₹',
            'low_stock' => 10,
            'working_hours' => 8,
            'sunday_off' => 'no',
            'grace_period' => 10,
            'lunch_break' => 30,
            'open_time' => '09:00',
            'close_time' => '18:00',
            'location_check_on_login' => 0,
        ]);

        $response = $this->postJson('/api/company-rules/update', [
            'selectedSubAdminId' => 'null',
            'working_hours' => 10.5,
            'sunday_off' => 'no',
            'grace_period' => 15,
            'lunch_break' => '00:00:00',
            'open_time' => '16:00',
            'close_time' => '23:59',
            'location_check_on_login' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Company rules updated successfully',
        ]);

        $this->assertDatabaseHas('settings', [
            'branch_id' => $admin->id,
            'working_hours' => 10.5,
            'grace_period' => 15,
            'open_time' => '16:00:00',
            'close_time' => '23:59:00',
        ]);
    }

    public function test_dashboard_settings_update_handles_undefined_and_null_strings(): void
    {
        $admin = $this->createBranchAndAdmin(34);
        Passport::actingAs($admin);

        Setting::create([
            'branch_id' => $admin->id,
            'name' => 'Dashboard Settings Branch 34',
            'email' => 'settings34@example.com',
            'phone' => '777777734',
            'address' => 'Main Office',
            'currency_position' => 'before',
            'currency_symbol' => '₹',
            'low_stock' => 10,
            'show_crm_dashboard' => 1,
            'show_hr_dashboard' => 1,
            'show_erp_dashboard' => 0,
        ]);

        $response = $this->postJson('/api/dashboard-settings/update', [
            'selectedSubAdminId' => 'null',
            'show_crm_dashboard' => 'undefined',
            'show_hr_dashboard' => 0,
            'show_erp_dashboard' => 1,
            'show_crm_lead_pipeline' => 'undefined',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Dashboard settings updated successfully',
        ]);

        // Assert that show_crm_dashboard remains 1 (not overwritten to 0/null/undefined)
        // and show_hr_dashboard is updated to 0, show_erp_dashboard is updated to 1
        $this->assertDatabaseHas('settings', [
            'branch_id' => $admin->id,
            'show_crm_dashboard' => 1,
            'show_hr_dashboard' => 0,
            'show_erp_dashboard' => 1,
        ]);
    }

    public function test_company_rules_store_handles_empty_strings_and_null_values(): void
    {
        $admin = $this->createBranchAndAdmin(35);
        Passport::actingAs($admin);

        $response = $this->postJson('/api/company-rules/store', [
            'id' => '',
            'payroll_type' => 'monthly',
            'working_hours_per_day' => '8',
            'half_day_hours' => '',
            'enable_overtime' => false,
            'overtime_rate_type' => 'multiplier',
            'overtime_multiplier' => '1.5',
            'min_overtime_count_in_minutes' => '0',
            'start_time' => '16:00',
            'end_time' => '23:59',
            'lunch_break' => '00:00:00',
            'grace_period' => '15',
            'sunday_off' => true,
            'sunday_pay_type' => 'unpaid',
            'saturday_off_enabled' => true,
            'saturday_off_type' => 'all',
            'saturday_off_pattern' => '',
            'saturday_pay_type' => 'regular',
            'saturday_half_day_enabled' => false,
            'saturday_half_day_pattern' => '',
            'include_holidays_in_working_days' => true,
            'enable_tax' => true,
            'tax_type' => 'fixed',
            'tax' => '',
            'salary_above_tax' => '',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Company rules created successfully.',
        ]);

        $this->assertDatabaseHas('company_rules', [
            'payroll_type' => 'monthly',
            'working_hours_per_day' => 8.00,
            'saturday_half_day_pattern' => '0',
            'start_time' => '16:00:00',
            'end_time' => '23:59:00',
            'lunch_break' => '00:00:00',
        ]);
    }
}
