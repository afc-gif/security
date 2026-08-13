<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;

use App\Models\Solution;
use Illuminate\Http\Request;

class SolutionController extends Controller
{
    public function index()
    {
        $solutions = Solution::orderBy('sort_order')->get();
        return view('admin.solutions.index', compact('solutions'));
    }

    public function create()
    {
        return view('admin.solutions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'required|string|max:10',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        Solution::create($validated);

        return redirect()->route('admin.solutions.index')
                        ->with('success', 'Solution category created successfully.');
    }

    public function show(Solution $solution)
    {
        $items = $solution->items()->get();
        return view('admin.solutions.show', compact('solution', 'items'));
    }

    public function edit(Solution $solution)
    {
        return view('admin.solutions.edit', compact('solution'));
    }

    public function update(Request $request, Solution $solution)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'required|string|max:10',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $solution->update($validated);

        return redirect()->route('admin.solutions.index')
                        ->with('success', 'Solution category updated successfully.');
    }

    public function destroy(Solution $solution)
    {
        $solution->delete();

        return redirect()->route('admin.solutions.index')
                        ->with('success', 'Solution category deleted successfully.');
    }
}
