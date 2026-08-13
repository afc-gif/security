<?php

namespace App\Http\Controllers\Commerce;

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
        try {
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

            if (empty($validated['barcode'])) {
                $validated['barcode'] = $this->generateUniqueBarcode();
            }
            $validated['display_on_website'] = $request->boolean('display_on_website');

            if ($request->hasFile('image')) {
                try {
                    $upload = $cloudinary->upload($request->file('image'), 'solutions');
                    $validated['image'] = $upload['url'];
                    $validated['image_public_id'] = $upload['public_id'];
                } catch (\Throwable $e) {
                    Log::error('Solution item image upload failed on create: ' . $e->getMessage(), ['exception' => $e]);
                    return back()->withErrors(['image' => 'Image upload failed. Please confirm Cloudinary is configured and try again.'])->withInput();
                }
            }

            $solution->items()->create($validated);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Solution item create failed: ' . $e->getMessage(), [
                'exception' => $e,
                'solution_id' => $solution->id,
            ]);
            return back()->withErrors(['error' => 'Could not save item right now. Please try again.'])->withInput();
        }

        return redirect()->route('admin.solutions.show', $solution)
                        ->with('success', 'Solution item created successfully.');
    }

    public function edit(Solution $solution, SolutionItem $item)
    {
        return view('admin.solutions.items.edit', compact('solution', 'item'));
    }

    public function update(Request $request, Solution $solution, SolutionItem $item, CloudinaryImageService $cloudinary)
    {
        try {
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
            $validated['display_on_website'] = $request->boolean('display_on_website');

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
                    Log::error('Solution item image upload failed on update: ' . $e->getMessage(), [
                        'exception' => $e,
                        'item_id' => $item->id,
                    ]);
                    return back()->withErrors(['image' => 'Image update failed. Please confirm Cloudinary is configured and try again.'])->withInput();
                }
            }

            $item->update($validated);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Solution item update failed: ' . $e->getMessage(), [
                'exception' => $e,
                'solution_id' => $solution->id,
                'item_id' => $item->id,
            ]);
            return back()->withErrors(['error' => 'Could not update item right now. Please try again.'])->withInput();
        }

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

    public function search(Request $request)
    {
        $q = $request->query('q', '');
        
        if (empty($q)) {
            return response()->json([]);
        }

        $products = SolutionItem::where('name', 'like', "%$q%")
            ->orWhere('barcode', 'like', "%$q%")
            ->select('id', 'name', 'price', 'barcode', 'stock', 'solution_id')
            ->with('solution:id,name')
            ->limit(10)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'solution_id' => $product->solution_id,
                    'category' => $product->solution ? $product->solution->name : 'Uncategorized',
                    'price' => number_format($product->price, 2),
                    'barcode' => $product->barcode,
                    'stock' => $product->stock,
                    'edit_url' => $product->solution_id
                        ? route('admin.solutions.items.edit', [$product->solution_id, $product->id])
                        : null,
                ];
            });

        return response()->json($products);
    }
}
