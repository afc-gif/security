<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Solution;
use App\Models\SolutionItem;
use Illuminate\Http\Request;

class SolutionItemController extends Controller
{
    public function create(Solution $solution)
    {
        return view('admin.solutions.items.create', compact('solution'));
    }

    public function store(Request $request, Solution $solution)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'sort_order' => 'nullable|integer',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('solutions', 'public');
            $validated['image'] = $path;
        }

        $solution->items()->create($validated);

        return redirect()->route('admin.solutions.show', $solution)
                        ->with('success', 'Solution item created successfully.');
    }

    public function edit(Solution $solution, SolutionItem $item)
    {
        return view('admin.solutions.items.edit', compact('solution', 'item'));
    }

    public function update(Request $request, Solution $solution, SolutionItem $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'sort_order' => 'nullable|integer',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('solutions', 'public');
            $validated['image'] = $path;
        }

        $item->update($validated);

        return redirect()->route('admin.solutions.show', $solution)
                        ->with('success', 'Solution item updated successfully.');
    }

    public function destroy(Solution $solution, SolutionItem $item)
    {
        $item->delete();

        return redirect()->route('admin.solutions.show', $solution)
                        ->with('success', 'Solution item deleted successfully.');
    }
}
