<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\CategoryChecklistTemplate;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    public function index()
    {
        $serviceCategories = ServiceCategory::query()
            ->with('checklistTemplates')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('admin.service-categories.index', compact('serviceCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:service_categories,name'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        ServiceCategory::create([
            'name' => trim($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Service category created.']);
        }

        return redirect()
            ->route('admin.service-categories.index')
            ->with('success', 'Service category created.');
    }

    public function update(Request $request, ServiceCategory $serviceCategory)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:service_categories,name,' . $serviceCategory->id],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $serviceCategory->update([
            'name' => trim($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category updated.',
                'name'    => $serviceCategory->name,
            ]);
        }

        return redirect()
            ->route('admin.service-categories.index')
            ->with('success', 'Service category updated.');
    }

    public function destroy(Request $request, ServiceCategory $serviceCategory)
    {
        if ($serviceCategory->jobRequestItems()->exists()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'This category is already used by jobs. Deactivate it instead of deleting it.'], 422);
            }
            return redirect()
                ->route('admin.service-categories.index')
                ->withErrors(['category' => 'This category is already used by jobs. Deactivate it instead of deleting it.']);
        }

        $serviceCategory->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Category deleted.']);
        }

        return redirect()
            ->route('admin.service-categories.index')
            ->with('success', 'Service category deleted.');
    }

    public function storeTemplate(Request $request, ServiceCategory $serviceCategory)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'input_type' => ['nullable', 'in:text,textarea,number,single_choice,multi_choice,photo'],
            'options' => ['nullable', 'string'],
            'is_required' => ['nullable', 'boolean'],
        ]);

        $serviceCategory->checklistTemplates()->create([
            'title' => trim($validated['title']),
            'description' => $validated['description'] ?? null,
            'input_type' => $validated['input_type'] ?? 'textarea',
            'options' => $this->parseOptions($validated['options'] ?? null),
            'is_required' => $request->boolean('is_required', true),
            'is_active' => true,
            'sort_order' => ((int) $serviceCategory->checklistTemplates()->max('sort_order')) + 1,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Checklist item added.']);
        }

        return redirect()
            ->route('admin.service-categories.index')
            ->with('success', 'Checklist template item added.');
    }

    public function updateTemplate(Request $request, CategoryChecklistTemplate $template)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'input_type' => ['nullable', 'in:text,textarea,number,single_choice,multi_choice,photo'],
            'options' => ['nullable', 'string'],
            'is_required' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $template->update([
            'title' => trim($validated['title']),
            'description' => $validated['description'] ?? null,
            'input_type' => $validated['input_type'] ?? 'textarea',
            'options' => $this->parseOptions($validated['options'] ?? null),
            'is_required' => $request->boolean('is_required'),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $validated['sort_order'] ?? $template->sort_order,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Checklist item updated.']);
        }

        return redirect()
            ->route('admin.service-categories.index')
            ->with('success', 'Checklist template item updated.');
    }

    public function destroyTemplate(Request $request, CategoryChecklistTemplate $template)
    {
        $template->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Checklist item deleted.']);
        }

        return redirect()
            ->route('admin.service-categories.index')
            ->with('success', 'Checklist template item deleted.');
    }

    private function parseOptions(?string $options): ?array
    {
        $parsed = collect(preg_split('/\r\n|\r|\n/', (string) $options))
            ->map(fn ($option) => trim($option))
            ->filter()
            ->values()
            ->all();

        return count($parsed) ? $parsed : null;
    }
}
