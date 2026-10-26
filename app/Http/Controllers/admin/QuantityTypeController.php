<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class QuantityTypeController extends Controller
{
    public function index(){
        return view('quantity_type.quantity');
    }
}
