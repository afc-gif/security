<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Category;
use App\Services\CloudinaryImageService;
use App\Support\ImageUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuItemController extends Controller
{
    public function index()
    {
        $menuItems = MenuItem::with('category')->orderBy('sort_order')->paginate(20);
        return view('admin.menu-items.index', compact('menuItems'));
    }

    public function create()
    {
        $categories = Category::where('active', true)->orderBy('sort_order')->get();
        return view('admin.menu-items.create', compact('categories'));
    }

    public function store(Request $request, CloudinaryImageService $cloudinary)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'price' => 'nullable|numeric|min:0',
            'sort_order' => 'integer',
            'available' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            try {
                $upload = $cloudinary->upload($request->file('image'), 'menu-items');
                $validated['image'] = $upload['url'];
                $validated['image_public_id'] = $upload['public_id'];
            } catch (\Throwable $e) {
                return back()->withErrors(['image' => 'Image upload failed. Please try again.'])->withInput();
            }
        }

        MenuItem::create($validated);

        return redirect()->route('menu-items.index')->with('success', 'Menu item created successfully');
    }

    public function edit(MenuItem $menuItem)
    {
        $categories = Category::where('active', true)->orderBy('sort_order')->get();
        return view('admin.menu-items.edit', compact('menuItem', 'categories'));
    }

    public function update(Request $request, MenuItem $menuItem, CloudinaryImageService $cloudinary)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'price' => 'nullable|numeric|min:0',
            'sort_order' => 'integer',
            'available' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            try {
                if ($menuItem->image_public_id) {
                    $cloudinary->destroy($menuItem->image_public_id);
                } elseif ($menuItem->image && !ImageUrl::isAbsolute($menuItem->image)) {
                    Storage::disk('public')->delete($menuItem->image);
                }

                $upload = $cloudinary->upload($request->file('image'), 'menu-items');
                $validated['image'] = $upload['url'];
                $validated['image_public_id'] = $upload['public_id'];
            } catch (\Throwable $e) {
                return back()->withErrors(['image' => 'Image update failed. Please try again.'])->withInput();
            }
        }

        $menuItem->update($validated);

        return redirect()->route('menu-items.index')->with('success', 'Menu item updated successfully');
    }

    public function uploadImage(Request $request, MenuItem $menuItem, CloudinaryImageService $cloudinary)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        try {
            if ($menuItem->image_public_id) {
                $cloudinary->destroy($menuItem->image_public_id);
            } elseif ($menuItem->image && !ImageUrl::isAbsolute($menuItem->image)) {
                Storage::disk('public')->delete($menuItem->image);
            }

            $upload = $cloudinary->upload($request->file('image'), 'menu-items');
            $menuItem->update([
                'image' => $upload['url'],
                'image_public_id' => $upload['public_id'],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Image upload failed.'], 422);
        }

        return response()->json(['success' => true, 'path' => $upload['url']]);
    }

    public function destroy(MenuItem $menuItem, CloudinaryImageService $cloudinary)
    {
        if ($menuItem->image_public_id) {
            $cloudinary->destroy($menuItem->image_public_id);
        } elseif ($menuItem->image && !ImageUrl::isAbsolute($menuItem->image)) {
            Storage::disk('public')->delete($menuItem->image);
        }
        $menuItem->delete();

        return redirect()->route('admin.menu-items.index')->with('success', 'Menu item deleted successfully');
    }
}
