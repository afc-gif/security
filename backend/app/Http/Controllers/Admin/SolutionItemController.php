<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Solution;
use App\Models\SolutionItem;
use App\Services\CloudinaryImageService;
use App\Support\ImageUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SolutionItemController extends Controller
{
    public function create(Solution $solution)
    {
        return view('admin.solutions.items.create', compact('solution'));
    }

    public function store(Request $request, Solution $solution, CloudinaryImageService $cloudinary)
    {
        Log::info("SolutionItemController.store - Raw request data", [
            'all_input' => $request->all(),
            'stock_input' => $request->input('stock'),
            'post_data' => $_POST ?? 'not available',
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:64|unique:solution_items,barcode',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'sort_order' => 'nullable|integer',
            'display_on_website' => 'nullable|boolean',
        ]);

        Log::info("SolutionItemController.store - Validated data", [
            'validated' => $validated,
            'stock_value' => $validated['stock'] ?? 'missing',
        ]);

        if (empty($validated['barcode'])) {
            $validated['barcode'] = $this->generateUniqueBarcode();
        }

        if ($request->hasFile('image')) {
            try {
                $upload = $cloudinary->upload($request->file('image'), 'solutions');
                $validated['image'] = $upload['url'];
                $validated['image_public_id'] = $upload['public_id'];
            } catch (\Throwable $e) {
                return back()->withErrors(['image' => 'Image upload failed. Please try again.'])->withInput();
            }
        }

        $solution->items()->create($validated);

        return redirect()->route('admin.solutions.show', $solution)
                        ->with('success', 'Solution item created successfully.');
    }

    public function edit(Solution $solution, SolutionItem $item)
    {
        return view('admin.solutions.items.edit', compact('solution', 'item'));
    }

    public function update(Request $request, Solution $solution, SolutionItem $item, CloudinaryImageService $cloudinary)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:64|unique:solution_items,barcode,' . $item->id,
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'sort_order' => 'nullable|integer',
            'display_on_website' => 'nullable|boolean',
        ]);

        if (empty($validated['barcode'])) {
            $validated['barcode'] = $item->barcode ?: $this->generateUniqueBarcode();
        }

        if ($request->hasFile('image')) {
            try {
                if ($item->image_public_id) {
                    $cloudinary->destroy($item->image_public_id);
                } elseif ($item->image && !ImageUrl::isAbsolute($item->image)) {
                    Storage::disk('public')->delete($item->image);
                }

                $upload = $cloudinary->upload($request->file('image'), 'solutions');
                $validated['image'] = $upload['url'];
                $validated['image_public_id'] = $upload['public_id'];
            } catch (\Throwable $e) {
                return back()->withErrors(['image' => 'Image update failed. Please try again.'])->withInput();
            }
        }

        $item->update($validated);

        return redirect()->route('admin.solutions.show', $solution)
                        ->with('success', 'Solution item updated successfully.');
    }

    public function destroy(Solution $solution, SolutionItem $item, CloudinaryImageService $cloudinary)
    {
        if ($item->image_public_id) {
            $cloudinary->destroy($item->image_public_id);
        } elseif ($item->image && !ImageUrl::isAbsolute($item->image)) {
            Storage::disk('public')->delete($item->image);
        }
        $item->delete();

        return redirect()->route('admin.solutions.show', $solution)
                        ->with('success', 'Solution item deleted successfully.');
    }

    private function generateUniqueBarcode(): string
    {
        do {
            $code = strtoupper(Str::random(10));
        } while (SolutionItem::where('barcode', $code)->exists());

        return $code;
    }
}
