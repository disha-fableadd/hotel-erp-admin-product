<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;

class CompanyRulesController extends Controller
{
    public function company_rules()
    {
        return view('company_rules.show');
    }

    public function display_rules()
    {
        return view('company_rules.company_rule_view');
    }

    public function create_rules()
    {
        return view('company_rules.create');
    }

    // Backward-compatible aliases
    public function show()
    {
        return $this->company_rules();
    }

    public function create()
    {
        return $this->create_rules();
    }
}
