<?php

namespace App\Http\Controllers\Field;

use App\Http\Controllers\Controller;

class FieldDashboardController extends Controller
{
    public function index()
    {
        return view('field.dashboard');
    }
}
