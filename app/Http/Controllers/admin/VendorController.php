<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VendorController extends Controller
{
    public function vendor_list(Request $request)
    {
        return view('supplier/supplierlist');
    }
    public function add_vendor(Request $request)
    {
        return view('supplier/addsupplier');
    }
    public function edit_vendor(Request $request)
    {
        return view('supplier/editsupplier');
    }
    public function vendor_report(Request $request)
    {
        return view('supplier/supplierreport');
    }
    public function vendor_view($id)
    {
        return view('supplier.view_vendor', ['id' => $id]);
    }
}
