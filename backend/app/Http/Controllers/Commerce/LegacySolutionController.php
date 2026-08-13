<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;

use App\Models\Solution;
use Illuminate\Http\Request;

class LegacySolutionController extends Controller
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

    public function apiIndex()
    {
        $solutions = Solution::where('active', true)
                             ->orderBy('sort_order')
                             ->with(['items' => function ($query) {
                                 $query->where('active', true)->orderBy('sort_order');
                             }])
                             ->get();

        return response()->json($solutions);
    }

    public function show(Solution $solution)
    {
        $solution->load('items');
        return view('solution-detail', compact('solution'));
    }
}
