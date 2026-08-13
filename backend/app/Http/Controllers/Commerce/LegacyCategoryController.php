<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;

use App\Models\Category;
use App\Services\CloudinaryImageService;
use App\Support\ImageUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LegacyCategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('sort_order')->paginate(15);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request, CloudinaryImageService $cloudinary)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:categories',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'sort_order' => 'integer',
            'active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            try {
                $upload = $cloudinary->upload($request->file('image'), 'categories');
                $validated['image'] = $upload['url'];
                $validated['image_public_id'] = $upload['public_id'];
            } catch (\Throwable $e) {
                return back()->withErrors(['image' => 'Image upload failed. Please try again.'])->withInput();
            }
        }

        $validated['slug'] = str()->slug($validated['name']);
        Category::create($validated);

        return redirect()->route('categories.index')->with('success', 'Category created successfully');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category, CloudinaryImageService $cloudinary)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'sort_order' => 'integer',
            'active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            try {
                if ($category->image_public_id) {
                    $cloudinary->destroy($category->image_public_id);
                } elseif ($category->image && !ImageUrl::isAbsolute($category->image)) {
                    Storage::disk('public')->delete($category->image);
                }
                $upload = $cloudinary->upload($request->file('image'), 'categories');
                $validated['image'] = $upload['url'];
                $validated['image_public_id'] = $upload['public_id'];
            } catch (\Throwable $e) {
                return back()->withErrors(['image' => 'Image update failed. Please try again.'])->withInput();
            }
        }

        $validated['slug'] = str()->slug($validated['name']);
        $category->update($validated);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully');
    }

    public function destroy(Category $category, CloudinaryImageService $cloudinary)
    {
        if ($category->image_public_id) {
            $cloudinary->destroy($category->image_public_id);
        } elseif ($category->image && !ImageUrl::isAbsolute($category->image)) {
            Storage::disk('public')->delete($category->image);
        }
        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully');
    }

    // API endpoints for live polling
    public function apiIndex()
    {
        return response()->json(
            Category::where('active', true)->orderBy('sort_order')->get()
        );
    }

    public function apiItems(Category $category)
    {
        return response()->json(
            $category->items()->where('available', true)->orderBy('sort_order')->get()
        );
    }
}
