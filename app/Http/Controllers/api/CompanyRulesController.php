<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\CompanyRulesModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyRulesController extends Controller
{
    public function rules()
    {
        $rules = CompanyRulesModel::query()->orderBy('id', 'desc')->get();

        if ($rules->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No rules found.',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'rules' => $rules,
            'data' => $rules,
        ]);
    }

    public function rules_get()
    {
        $rules = CompanyRulesModel::query()->orderBy('id', 'desc')->first();

        if (! $rules) {
            return response()->json([
                'status' => 'error',
                'message' => 'No rules found.',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $rules,
        ]);
    }

    public function store(Request $request)
    {
        $payload = $this->getPayload($request);

        $validator = Validator::make($payload, [
            'working_hours_per_day' => 'required|numeric|min:0.1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 400);
        }

        $saveData = $this->prepareSaveData($payload);

        try {
            $rule = null;

            if (! empty($payload['id'])) {
                $rule = CompanyRulesModel::query()->find($payload['id']);
            }

            if ($rule) {
                $rule->update($saveData);
                $message = 'Company rules updated successfully.';
            } else {
                $rule = CompanyRulesModel::query()->create($saveData);
                $message = 'Company rules created successfully.';
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $rule,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save company rules: '.$e->getMessage(),
            ], 500);
        }
    }

    public function edit(int $id)
    {
        $rule = CompanyRulesModel::query()->find($id);

        if (! $rule) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rule not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $rule,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $request->merge(['id' => $id]);
        return $this->store($request);
    }

    public function delete(int $id)
    {
        $rule = CompanyRulesModel::query()->find($id);

        if (! $rule) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rule not found.',
            ], 404);
        }

        $rule->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Rule deleted successfully.',
        ]);
    }

    public function calculateSalary(Request $request)
    {
        $payload = $this->getPayload($request);
        $rules = CompanyRulesModel::query()->orderBy('id', 'desc')->first();

        if (! $rules) {
            return response()->json([
                'status' => 'error',
                'message' => 'Company rules not configured.',
            ], 400);
        }

        $baseSalary = (float) ($payload['base_salary'] ?? 0);
        $workedHours = (float) ($payload['worked_hours'] ?? 0);
        $workedDays = (float) ($payload['worked_days'] ?? 0);
        $workingHoursPerDay = (float) ($rules->working_hours_per_day ?: 8);

        $calculations = [
            'base_salary' => $baseSalary,
            'gross_salary' => $baseSalary,
            'deductions' => [],
            'allowances' => [],
            'net_salary' => $baseSalary,
        ];

        if ($rules->payroll_type === 'hourly' && $workingHoursPerDay > 0) {
            $calculations['gross_salary'] = $workedHours * ($baseSalary / ($workingHoursPerDay * 26));
        } elseif ($rules->payroll_type === 'daily') {
            $calculations['gross_salary'] = $workedDays * ($baseSalary / 26);
        }

        if ((bool) $rules->enable_tax && $calculations['gross_salary'] > (float) ($rules->salary_above_tax ?? 0)) {
            $taxAmount = 0.0;
            if ($rules->tax_type === 'fixed') {
                $taxAmount = (float) ($rules->tax ?? 0);
            } elseif ($rules->tax_type === 'percentage') {
                $taxAmount = ($calculations['gross_salary'] * (float) ($rules->tax ?? 0)) / 100;
            }
            $calculations['deductions']['tax'] = $taxAmount;
        }

        if ((bool) ($rules->enable_pf ?? false)) {
            $pfAmount = ($calculations['gross_salary'] * (float) ($rules->employee_pf ?? 0)) / 100;
            $calculations['deductions']['pf'] = $pfAmount;
        }

        if ((bool) ($rules->enable_esi ?? false)) {
            $esiAmount = ($calculations['gross_salary'] * (float) ($rules->employee_esi ?? 0)) / 100;
            $calculations['deductions']['esi'] = $esiAmount;
        }

        $totalDeductions = array_sum($calculations['deductions']);
        $calculations['net_salary'] = $calculations['gross_salary'] - $totalDeductions;

        return response()->json([
            'status' => 'success',
            'data' => $calculations,
        ]);
    }

    public function validateAttendance(Request $request)
    {
        $payload = $this->getPayload($request);
        $rules = CompanyRulesModel::query()->orderBy('id', 'desc')->first();

        if (! $rules) {
            return response()->json([
                'status' => 'error',
                'message' => 'Company rules not configured.',
            ], 400);
        }

        if (empty($payload['check_in_time']) || empty($payload['check_out_time'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'check_in_time and check_out_time are required.',
            ], 422);
        }

        $checkIn = strtotime((string) $payload['check_in_time']);
        $checkOut = strtotime((string) $payload['check_out_time']);

        if ($checkIn === false || $checkOut === false) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid time format provided.',
            ], 422);
        }

        $response = [
            'is_late' => false,
            'is_half_day' => false,
            'is_full_day' => false,
            'overtime_hours' => 0,
            'status' => 'present',
        ];

        if (! empty($rules->start_time)) {
            $startTime = strtotime((string) $rules->start_time);
            $graceTime = $startTime + ((int) ($rules->grace_period ?? 0) * 60);
            if ($checkIn > $graceTime) {
                $response['is_late'] = true;
                $response['late_minutes'] = (int) round(($checkIn - $startTime) / 60);
            }
        }

        $workedSeconds = max(0, $checkOut - $checkIn);
        $workedSeconds -= $this->parseDurationToSeconds((string) ($rules->lunch_break ?? ''));
        $workedSeconds = max(0, $workedSeconds);
        $workedHours = $workedSeconds / 3600;

        $workingHoursPerDay = (float) ($rules->working_hours_per_day ?: 8);
        $halfDayHours = (float) ($rules->half_day_hours ?: ($workingHoursPerDay / 2));

        if ($workedHours >= $workingHoursPerDay) {
            $response['is_full_day'] = true;

            if ((bool) $rules->enable_overtime && $workedHours > $workingHoursPerDay) {
                $overtimeMinutes = ($workedHours - $workingHoursPerDay) * 60;
                $minOvertimeMinutes = (int) ($rules->min_overtime_count_in_minutes ?? 0);
                if ($overtimeMinutes >= $minOvertimeMinutes) {
                    $response['overtime_hours'] = round($workedHours - $workingHoursPerDay, 2);
                }
            }
        } elseif ($workedHours >= $halfDayHours) {
            $response['is_half_day'] = true;
            $response['status'] = 'half_day';
        } else {
            $response['status'] = 'insufficient_hours';
        }

        $response['worked_hours'] = round($workedHours, 2);

        return response()->json([
            'status' => 'success',
            'data' => $response,
        ]);
    }

    private function getPayload(Request $request): array
    {
        $payload = $request->json()->all();
        if (empty($payload)) {
            $payload = $request->all();
        }

        return is_array($payload) ? $payload : [];
    }

    private function prepareSaveData(array $data): array
    {
        $saturdayOffType = (string) ($data['saturday_off_type'] ?? 'all');
        $saturdayOffPattern = $data['saturday_off_pattern'] ?? null;

        if ($saturdayOffPattern === null || $saturdayOffPattern === '') {
            if ($saturdayOffType === 'alternate-even') {
                $saturdayOffPattern = '2,4';
            } elseif ($saturdayOffType === 'alternate-odd') {
                $saturdayOffPattern = '1,3,5';
            } else {
                $saturdayOffPattern = '0,0';
            }
        }

        return [
            'payroll_type' => $data['payroll_type'] ?? 'monthly',
            'working_hours_per_day' => (float) ($data['working_hours_per_day'] ?? 0),
            'half_day_hours' => isset($data['half_day_hours']) && $data['half_day_hours'] !== '' ? (float) $data['half_day_hours'] : null,
            'enable_overtime' => $this->toBoolInt($data['enable_overtime'] ?? true, true),
            'overtime_rate_type' => $data['overtime_rate_type'] ?? 'multiplier',
            'overtime_multiplier' => isset($data['overtime_multiplier']) && $data['overtime_multiplier'] !== '' ? (float) $data['overtime_multiplier'] : 1.5,
            'min_overtime_count_in_minutes' => (int) ($data['min_overtime_count_in_minutes'] ?? 30),
            'start_time' => $data['start_time'] ?? '09:30:00',
            'end_time' => $data['end_time'] ?? '18:30:00',
            'lunch_break' => $data['lunch_break'] ?? '00:30:00',
            'grace_period' => (int) ($data['grace_period'] ?? 0),
            'sunday_off' => $this->toBoolInt($data['sunday_off'] ?? true, true),
            'sunday_pay_type' => $data['sunday_pay_type'] ?? 'unpaid',
            'saturday_off_enabled' => $this->toBoolInt($data['saturday_off_enabled'] ?? true, true),
            'saturday_off_type' => $saturdayOffType,
            'saturday_off_pattern' => (string) $saturdayOffPattern,
            'saturday_pay_type' => $data['saturday_pay_type'] ?? 'regular',
            'saturday_half_day_enabled' => $this->toBoolInt($data['saturday_half_day_enabled'] ?? false, false),
            'saturday_half_day_pattern' => ($data['saturday_half_day_pattern'] !== null && $data['saturday_half_day_pattern'] !== '') ? (string) $data['saturday_half_day_pattern'] : '0',
            'yearly_holidays' => isset($data['yearly_holidays']) && $data['yearly_holidays'] !== '' ? (int) $data['yearly_holidays'] : 0,
            'enable_tax' => $this->toBoolInt($data['enable_tax'] ?? false, false),
            'tax_type' => $data['tax_type'] ?? 'fixed',
            'tax' => isset($data['tax']) && $data['tax'] !== '' ? (float) $data['tax'] : 0,
            'salary_above_tax' => isset($data['salary_above_tax']) && $data['salary_above_tax'] !== '' ? (float) $data['salary_above_tax'] : 0,
            'include_holidays_in_working_days' => $this->toBoolInt($data['include_holidays_in_working_days'] ?? false, false),
        ];
    }

    private function toBoolInt($value, bool $default): int
    {
        if ($value === null || $value === '') {
            return $default ? 1 : 0;
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if (is_numeric($value)) {
            return ((int) $value) > 0 ? 1 : 0;
        }

        $normalized = strtolower(trim((string) $value));
        return in_array($normalized, ['1', 'true', 'yes', 'on'], true) ? 1 : 0;
    }

    private function parseDurationToSeconds(string $duration): int
    {
        if ($duration === '') {
            return 0;
        }

        $parts = explode(':', $duration);
        if (count($parts) < 2) {
            return 0;
        }

        $hours = (int) ($parts[0] ?? 0);
        $minutes = (int) ($parts[1] ?? 0);
        $seconds = (int) ($parts[2] ?? 0);

        return ($hours * 3600) + ($minutes * 60) + $seconds;
    }
}
