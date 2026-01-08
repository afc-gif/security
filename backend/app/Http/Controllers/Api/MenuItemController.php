<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SolutionItem;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MenuItemController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = SolutionItem::with('solution')
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
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeImage($request);
        }

        $data['barcode'] = $data['barcode'] ?? $this->generateBarcode();

        $payload = [
            'solution_id' => $data['category_id'] ?? null,
            'name' => $data['name'],
            'barcode' => $data['barcode'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'image' => $data['image'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'active' => $data['is_active'] ?? true,
            'is_sold_out' => $data['is_sold_out'] ?? false,
        ];

        if (array_key_exists('stock', $data) && $data['stock'] !== null) {
            $payload['stock'] = (int) $data['stock'];
        }

        $item = SolutionItem::create($payload);

        $item->load('solution');

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
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeImage($request);
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
            'sort_order' => $data['sort_order'] ?? $menuItem->sort_order,
            'active' => array_key_exists('is_active', $data) ? $data['is_active'] : $menuItem->active,
            'is_sold_out' => array_key_exists('is_sold_out', $data) ? $data['is_sold_out'] : $menuItem->is_sold_out,
        ];

        if (array_key_exists('stock', $data)) {
            $payload['stock'] = $data['stock'] === null ? 0 : (int) $data['stock'];
        }

        $menuItem->update($payload);

        $menuItem->refresh()->load('solution');

        return response()->json($this->transformItem($menuItem));
    }

    public function regenerateBarcode(SolutionItem $menuItem)
    {
        $menuItem->update(['barcode' => $this->generateBarcode()]);

        return response()->json($this->transformItem($menuItem->load('solution')));
    }

    public function lookup(Request $request)
    {
        $data = $request->validate([
            'barcode' => 'required|string',
        ]);

        try {
            $item = SolutionItem::with('solution')
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

        if ($item->is_sold_out) {
            return response()->json(['message' => 'Item is sold out'], 409);
        }

        return response()->json($this->transformItem($item));
    }

    public function toggleSoldOut(SolutionItem $menuItem)
    {
        $menuItem->update(['is_sold_out' => ! (bool) $menuItem->is_sold_out]);

        return response()->json($this->transformItem($menuItem->load('solution')));
    }

    public function destroy(SolutionItem $menuItem)
    {
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

    private function storeImage(Request $request): string
    {
        $path = $request->file('image')->store('solution-items', 'public');

        return $path;
    }

    private function transformItem(SolutionItem $item): array
    {
        return [
            'id' => $item->id,
            'category_id' => $item->solution_id,
            'name' => $item->name,
            'description' => $item->description,
            'price' => $item->price,
            'barcode' => $item->barcode,
            'is_sold_out' => (bool) $item->is_sold_out,
            'stock' => $item->stock,
            'is_active' => (bool) $item->active,
            'sort_order' => $item->sort_order,
            'image_url' => $item->image ? Storage::disk('public')->url($item->image) : null,
            'category' => $item->solution ? [
                'id' => $item->solution->id,
                'name' => $item->solution->name,
            ] : null,
        ];
    }
}
