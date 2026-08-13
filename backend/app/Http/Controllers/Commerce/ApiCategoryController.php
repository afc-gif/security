<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;

use App\Models\Solution;
use Illuminate\Http\Request;

class ApiCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Solution::query()->orderBy('sort_order')->orderBy('name');

        if ($request->boolean('active_only', false)) {
            $query->where('active', true);
        }

        return $query->get()->map(function (Solution $solution) {
            return [
                'id' => $solution->id,
                'name' => $solution->name,
                'description' => $solution->description,
                'is_active' => (bool) $solution->active,
                'sort_order' => $solution->sort_order,
            ];
        });
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $solution = Solution::create([
            'name' => $data['name'],
            'icon' => $data['icon'] ?? 'SEC',
            'description' => $data['description'] ?? null,
            'active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return response()->json([
            'id' => $solution->id,
            'name' => $solution->name,
            'description' => $solution->description,
            'is_active' => (bool) $solution->active,
            'sort_order' => $solution->sort_order,
        ], 201);
    }

    public function update(Request $request, Solution $category)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'icon' => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $category->update([
            'name' => $data['name'] ?? $category->name,
            'icon' => $data['icon'] ?? $category->icon,
            'description' => array_key_exists('description', $data) ? $data['description'] : $category->description,
            'active' => $data['is_active'] ?? $category->active,
            'sort_order' => $data['sort_order'] ?? $category->sort_order,
        ]);

        return response()->json([
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->description,
            'is_active' => (bool) $category->active,
            'sort_order' => $category->sort_order,
        ]);
    }

    public function destroy(Solution $category)
    {
        $category->delete();

        return response()->noContent();
    }
}
