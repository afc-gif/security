<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;

use App\Models\SolutionItem;
use App\Services\CloudinaryImageService;
use App\Support\ImageUrl;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApiMenuItemController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = SolutionItem::with(['solution', 'product'])
                ->orderBy('sort_order')
                ->orderBy('name');

            if ($request->filled('category_id')) {
                $query->where('solution_id', $request->input('category_id'));
            }

            if ($request->boolean('active_only', false)) {
                $query->where('active', true);
            }

            return $query->get()->map(fn (SolutionItem $item) => $this->transformItem($item));
        } catch (QueryException $e) {
            report($e);
            return response()->json(['message' => 'Menu items unavailable (database not ready).'], 503);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:solutions,id',
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:64|unique:solution_items,barcode',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_sold_out' => 'sometimes|boolean',
            'stock' => 'nullable|integer|min:0',
            'image_url' => 'nullable|url',
            'image' => 'nullable|image|max:4096',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'display_on_website' => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            $upload = $this->storeImage($request, $cloudinary = app(CloudinaryImageService::class));
            $data['image'] = $upload['image'];
            $data['image_public_id'] = $upload['image_public_id'];
        }

        $data['barcode'] = $data['barcode'] ?? $this->generateBarcode();

        $payload = [
            'solution_id' => $data['category_id'] ?? null,
            'name' => $data['name'],
            'barcode' => $data['barcode'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'image' => $data['image'] ?? null,
            'image_public_id' => $data['image_public_id'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'active' => $data['is_active'] ?? true,
            'is_sold_out' => $data['is_sold_out'] ?? false,
            'stock' => (int) ($data['stock'] ?? 0),
            'display_on_website' => array_key_exists('display_on_website', $data) ? (bool) $data['display_on_website'] : true,
        ];

        $item = SolutionItem::create($payload);

        $item->load(['solution', 'product']);

        return response()->json($this->transformItem($item), 201);
    }

    public function update(Request $request, SolutionItem $menuItem)
    {
        $data = $request->validate([
            'category_id' => 'sometimes|required|exists:solutions,id',
            'name' => 'sometimes|required|string|max:255',
            'barcode' => 'nullable|string|max:64|unique:solution_items,barcode,' . $menuItem->id,
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'is_sold_out' => 'sometimes|boolean',
            'stock' => 'nullable|integer|min:0',
            'image_url' => 'nullable|url',
            'image' => 'nullable|image|max:4096',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'display_on_website' => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($menuItem->image_public_id) {
                app(CloudinaryImageService::class)->destroy($menuItem->image_public_id);
            } elseif ($menuItem->image && !ImageUrl::isAbsolute($menuItem->image)) {
                Storage::disk('public')->delete($menuItem->image);
            }

            $upload = $this->storeImage($request, $cloudinary = app(CloudinaryImageService::class));
            $data['image'] = $upload['image'];
            $data['image_public_id'] = $upload['image_public_id'];
        }

        if (empty($data['barcode']) && empty($menuItem->barcode)) {
            $data['barcode'] = $this->generateBarcode();
        }

        $payload = [
            'solution_id' => $data['category_id'] ?? $menuItem->solution_id,
            'name' => $data['name'] ?? $menuItem->name,
            'barcode' => $data['barcode'] ?? $menuItem->barcode,
            'description' => array_key_exists('description', $data) ? $data['description'] : $menuItem->description,
            'price' => array_key_exists('price', $data) ? $data['price'] : $menuItem->price,
            'image' => $data['image'] ?? $menuItem->image,
            'image_public_id' => $data['image_public_id'] ?? $menuItem->image_public_id,
            'sort_order' => $data['sort_order'] ?? $menuItem->sort_order,
            'active' => array_key_exists('is_active', $data) ? $data['is_active'] : $menuItem->active,
            'is_sold_out' => array_key_exists('is_sold_out', $data) ? $data['is_sold_out'] : $menuItem->is_sold_out,
            'display_on_website' => array_key_exists('display_on_website', $data) ? (bool) $data['display_on_website'] : $menuItem->display_on_website,
        ];

        if (array_key_exists('stock', $data)) {
            $payload['stock'] = $data['stock'] === null ? 0 : (int) $data['stock'];
        }

        $menuItem->update($payload);

        $menuItem->refresh()->load(['solution', 'product']);

        return response()->json($this->transformItem($menuItem));
    }

    public function regenerateBarcode(SolutionItem $menuItem)
    {
        $menuItem->update(['barcode' => $this->generateBarcode()]);

        return response()->json($this->transformItem($menuItem->load(['solution', 'product'])));
    }

    public function lookup(Request $request)
    {
        $data = $request->validate([
            'barcode' => 'required|string',
        ]);

        try {
            $item = SolutionItem::with(['solution', 'product'])
                ->where('barcode', $data['barcode'])
                ->first();
        } catch (QueryException $e) {
            report($e);
            return response()->json(['message' => 'Menu lookup unavailable (database not ready).'], 503);
        }

        if (! $item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        if (! $item->active) {
            return response()->json(['message' => 'Item is inactive'], 404);
        }

        return response()->json($this->transformItem($item));
    }

    public function search(Request $request)
    {
        $query = $request->validate([
            'q' => 'required|string|min:2',
        ]);

        try {
            $items = SolutionItem::with(['solution', 'product'])
                ->where('active', true)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', '%' . $query['q'] . '%')
                      ->orWhere('barcode', 'like', '%' . $query['q'] . '%');
                })
                ->orderBy('name')
                ->limit(10)
                ->get();
        } catch (QueryException $e) {
            report($e);
            return response()->json(['message' => 'Search unavailable (database not ready).'], 503);
        }

        return response()->json($items->map(fn (SolutionItem $item) => $this->transformItem($item)));
    }

    public function toggleSoldOut(SolutionItem $menuItem)
    {
        $menuItem->update(['is_sold_out' => ! (bool) $menuItem->is_sold_out]);

        return response()->json($this->transformItem($menuItem->load(['solution', 'product'])));
    }

    public function toggleDisplayOnWebsite(SolutionItem $menuItem)
    {
        $menuItem->update(['display_on_website' => ! (bool) $menuItem->display_on_website]);

        return response()->json($this->transformItem($menuItem->load(['solution', 'product'])));
    }

    public function destroy(SolutionItem $menuItem)
    {
        if ($menuItem->image_public_id) {
            app(CloudinaryImageService::class)->destroy($menuItem->image_public_id);
        } elseif ($menuItem->image && !ImageUrl::isAbsolute($menuItem->image)) {
            Storage::disk('public')->delete($menuItem->image);
        }

        $menuItem->delete();

        return response()->noContent();
    }

    private function generateBarcode(): string
    {
        $attempts = 0;
        do {
            $barcode = strtoupper(Str::random(10));
            $attempts++;
        } while ($attempts < 10 && SolutionItem::where('barcode', $barcode)->exists());

        if ($attempts >= 10) {
            $barcode = 'BC-' . time() . '-' . random_int(100, 999);
        }

        return $barcode;
    }

    private function storeImage(Request $request, CloudinaryImageService $cloudinary): array
    {
        $upload = $cloudinary->upload($request->file('image'), 'solutions');

        return [
            'image' => $upload['url'],
            'image_public_id' => $upload['public_id'],
        ];
    }

    private function transformItem(SolutionItem $item): array
    {
        $price = $this->resolvePosPrice($item);

        return [
            'id' => $item->id,
            'category_id' => $item->solution_id,
            'name' => $item->name,
            'description' => $item->description,
            'price' => $price,
            'unit_price' => $price,
            'barcode' => $item->barcode,
            'is_sold_out' => (bool) $item->is_sold_out,
            'stock' => $item->stock,
            'is_active' => (bool) $item->active,
            'sort_order' => $item->sort_order,
            'image_url' => ImageUrl::url($item->image),
            'display_on_website' => (bool) $item->display_on_website,
            'category' => $item->solution ? [
                'id' => $item->solution->id,
                'name' => $item->solution->name,
            ] : null,
        ];
    }

    private function resolvePosPrice(SolutionItem $item): float
    {
        foreach ([$item->price, $item->product?->price] as $candidate) {
            if ($candidate === null || $candidate === '') {
                continue;
            }

            if (is_numeric($candidate)) {
                return (float) $candidate;
            }
        }

        return 0.0;
    }
}
