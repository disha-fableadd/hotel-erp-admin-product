<style>
    @page { size: A4; margin: 15mm; }
    body { font-family: 'Arial', sans-serif; color: #222; margin: 0; padding: 0; }
    .slip { width: 100%; margin: 0 auto; }
    .header { border: 1px solid #ddd; padding: 12px 14px; background: #fff; }
    .company-row { display: flex; justify-content: space-between; align-items: flex-start; }
    .company-logo { width: 60px; height: auto; margin-right: 15px; }
    .company-info { flex-grow: 1; }
    .company-name { font-size: 20px; font-weight: 700; margin: 0; }
    .company-address { font-size: 11px; margin-top: 4px; color: #555; line-height: 1.4; }
    .title-row { display: flex; justify-content: space-between; align-items: center; margin-top: 10px; background: #f36e21; padding: 10px 12px; color: #fff; }
    .title-row .title { font-size: 14px; font-weight: 700; }
    .title-row .month { font-size: 12px; }
    .box { border: 1px solid #ddd; background: #fff; padding: 12px; margin-top: 10px; }
    .table-grid { width: 100%; border-collapse: collapse; font-size: 12px; }
    .table-grid td { border: 1px solid #ddd; padding: 8px 10px; vertical-align: middle; }
    .table-grid .label { width: 18%; font-weight: 700; background: #f7f7f7; }
    .table-grid .value { width: 32%; }
    .summary-table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 12px; border: 1px solid #ddd; }
    .summary-table th, .summary-table td { border: 1px solid #ddd; padding: 8px 10px; }
    .summary-table th { background: #f8b19b; color: #222; font-weight: 700; }
    .summary-table .label-col { width: 30%; }
    .summary-table .value-col { width: 20%; text-align: right; }
    .total-row td { background: #f5f5f5; font-weight: 700; }
    .deduction-row td { color: #c0392b; }
    .net-salary-row td { background: #fef6f4; font-weight: 700; color: #c0392b; border-top: 2px solid #f8b19b; }
    .total-highlight { background: #faf2ef; }
    .footer-table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 12px; }
    .footer-table td { border: 1px solid #ddd; padding: 8px 10px; }
    .footer-table .signature { height: 40px; vertical-align: bottom; }
</style>

<div class="slip">
    <div class="header">
        <table style="width: 100%; border-collapse: collapse; border: none;">
            <tr>
                <td style="border: none; padding: 0; vertical-align: top;">
                    <div style="display: flex; align-items: flex-start;">
                        <!-- @if($company['logo'])
                            <img src="{{ $company['logo'] }}" alt="Logo" class="company-logo" style="margin-right: 15px;">
                        @endif -->
                        <div class="company-info">
                            <div class="company-name">{{ $company['company_name'] }}</div>
                            <div class="company-address">{{ $company['company_address'] }}</div>
                        </div>
                    </div>
                </td>
                <td style="border: none; padding: 0; vertical-align: top; text-align: right; font-size: 11px; color: #444;">
                    {{ now()->format('d M Y') }}
                </td>
            </tr>
        </table>
        <div class="title-row">
            <div class="title">Salary Slip</div>
            <div class="month">Month: {{ \Carbon\Carbon::parse($payroll['month_year'] . '-01')->format('Y-m') }}</div>
        </div>
    </div>

    <div class="box">
        <table class="table-grid">
            <tr>
                <td class="label">Staff Name</td>
                <td class="value">{{ $user['firstname'] ?? '' }}</td>
                <td class="label">Staff Code</td>
                <td class="value">EMP#{{ $user['employee_id'] ?? $payroll['user_id'] ?? '' }}</td>
            </tr>
            <tr>
                <td class="label">Designation</td>
                <td class="value">{{ $designation['designation_name'] ?? '' }}</td>
                <td class="label">Department</td>
                <td class="value">{{ $department['department_name'] ?? '' }}</td>
            </tr>
            <tr>
                <td class="label">Date of Joining</td>
                <td class="value">{{ $user['joining_date'] ?? '' }}</td>
                <td class="label">Working Days</td>
                <td class="value">{{ $calculatedData['working_days'] ?? 0 }} | Present: {{ $calculatedData['present_days'] ?? 0 }} | Absent: {{ $calculatedData['absent_days'] ?? 0 }}</td>
            </tr>
            <tr>
                <td class="label">Total HAlf Day</td>
                <!-- <td class="value" colspan="3">Total Leaves: {{ $calculatedData['total_leaves'] ?? 0 }} | Half Day: {{ $calculatedData['half_days'] ?? 0 }}</td> -->
                <td class="value" colspan="3"> Half Day: {{ $calculatedData['half_days'] ?? 0 }}</td>
            </tr>
        </table>
    </div>

    <div class="box">
        @php
            $basicSalary     = $payroll['salary_amount'] ?? 0;
            $overtimePay     = $calculatedData['overtime_pay'] ?? 0;
            $overtimeHours   = $payroll['total_overtime_hours'] ?? $calculatedData['total_overtime_hours'] ?? 0;
            $taxDeduction    = $calculatedData['tax_deduction'] ?? 0;
            $salaryDeduction = $calculatedData['salary_deduction'] ?? 0;
            $advancePayment  = $calculatedData['advance_payment'] ?? $payroll['advance_payment'] ?? 0;
            $totalEarnings   = $basicSalary + $overtimePay;
            $netSalary       = $totalEarnings - $taxDeduction - $salaryDeduction - $advancePayment;
        @endphp
        <table class="summary-table">
            <tr>
                <th class="label-col">Earnings</th>
                <th class="value-col">Amount</th>
            </tr>
            {{-- Basic Salary --}}
            <tr>
                <td>Basic Salary</td>
                <td class="value-col">{{ number_format($basicSalary, 2) }}</td>
            </tr>
            {{-- Overtime Pay --}}
            <tr>
                <td>Overtime Pay</td>
                <td class="value-col">{{ number_format($overtimePay, 2) }}</td>
            </tr>
            {{-- Total Overtime Hours --}}
            <tr>
                <td>Total Overtime Hours</td>
                <td class="value-col">{{ number_format($overtimeHours, 2) }} hrs</td>
            </tr>
            {{-- Total Earnings (bold) --}}
            <tr class="total-row">
                <td><strong>Total Earnings</strong> <small style="font-weight:normal;color:#888;">(Basic Salary + Overtime Pay)</small></td>
                <td class="value-col"><strong>{{ number_format($totalEarnings, 2) }}</strong></td>
            </tr>
            {{-- Tax Deduction (red) --}}
            <tr class="deduction-row">
                <td>Tax Deduction</td>
                <td class="value-col">{{ number_format($taxDeduction, 2) }}</td>
            </tr>
            {{-- Salary Deduction (red) --}}
            <tr class="deduction-row">
                <td>Salary Deduction</td>
                <td class="value-col">{{ number_format($salaryDeduction, 2) }}</td>
            </tr>
            {{-- Advance Payment (red) --}}
            <tr class="deduction-row">
                <td>Advance Payment</td>
                <td class="value-col">{{ number_format($advancePayment, 2) }}</td>
            </tr>
            {{-- Net Salary (bold, highlighted) --}}
            <tr class="net-salary-row">
                <td><strong>Net Salary</strong> <small style="font-weight:normal;color:#999;">(Total Earnings - Deductions)</small></td>
                <td class="value-col"><strong>{{ number_format($netSalary, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    {{-- Gross Salary & Net Salary summary box --}}
    <div class="box total-highlight" style="margin-top: 10px;">
        <table class="table-grid">
            <tr>
                <td class="label">Gross Salary</td>
                <td class="value">{{ number_format($totalEarnings, 2) }}</td>
                <td class="label">Net Salary</td>
                <td class="value" style="font-weight:700; color:#c0392b;">{{ number_format($netSalary, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td><strong>Payment Status:</strong> {{ ucfirst($payroll['payment_status'] ?? 'Paid') }}</td>
                <td><strong>Payment Date:</strong> {{ isset($payroll['payment_date']) ? \Carbon\Carbon::parse($payroll['payment_date'])->format('Y-m-d') : '-' }}</td>
            </tr>
            <tr>
                <td colspan="2" class="signature">Authorized By</td>
            </tr>
        </table>
    </div>
</div>