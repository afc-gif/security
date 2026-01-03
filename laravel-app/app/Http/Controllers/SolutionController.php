<?php

namespace App\Http\Controllers;

use App\Models\Solution;
use Illuminate\Http\Request;

class SolutionController extends Controller
{
    public function index()
    {
        $solutions = Solution::where('active', true)
                             ->orderBy('sort_order')
                             ->with(['items' => function ($query) {
                                 $query->where('active', true)->orderBy('sort_order');
                             }])
                             ->get();
        
        return view('solutions', compact('solutions'));
    }

    public function show(Solution $solution)
    {
        $solution->load('items');
        return view('solution-detail', compact('solution'));
    }
}
