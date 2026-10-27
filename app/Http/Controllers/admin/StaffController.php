<?php

namespace App\Http\Controllers\admin;

use App\Models\DepartmentModel;
use App\Models\DesignationModel;
use App\Models\Module;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StaffController extends Controller
{
    public function staff_list(Request $request)
    {
        return view('staff.staff.stafflist');
    }
    public function add_staff(Request $request)
    {
        if (!can_create_user()) {
            $limit = plan_user_limit();
            return redirect()->route('staff.list')->with('error', "You have reached the maximum user limit ({$limit}) allowed by your current plan. Please upgrade your plan to add more staff.");
        }

        $modules = Module::all();
        $departments = DepartmentModel::query()
            ->orderBy('department_name')
            ->get(['id', 'department_name']);
        $designations = DesignationModel::query()
            ->orderBy('designation_name')
            ->get(['id', 'designation_name', 'department_id']);

        return view('staff.staff.addstaff', compact('modules', 'departments', 'designations'));
    }
    public function edit_staff(Request $request, $num = null)
    {
        $departments = DepartmentModel::query()
            ->orderBy('department_name')
            ->get(['id', 'department_name']);
        $designations = DesignationModel::query()
            ->orderBy('designation_name')
            ->get(['id', 'designation_name', 'department_id']);

        return view('staff.staff.editstaff', compact('departments', 'designations'));
    }
    public function staff_report(Request $request)
    {
        return view('staff/staffreport');
    }
    public function staff_view($id)
    {
        return view('staff.staff.view_staff', ['id' => $id]);
    }
}
