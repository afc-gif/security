<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Installation;
use App\Services\CloudinaryImageService;
use App\Support\ImageUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InstallationController extends Controller
{
    public function index()
    {
        $installations = Installation::orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('completed_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.installations.index', compact('installations'));
    }

    public function create()
    {
        return view('admin.installations.create');
    }

    public function store(Request $request, CloudinaryImageService $cloudinary)
    {
        $validated = $this->validatePayload($request);
        $validated['slug'] = $this->buildUniqueSlug($validated['slug'] ?? null, $validated['title']);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_public'] = $request->boolean('is_public', true);

        try {
            if ($request->hasFile('cover_image')) {
                $upload = $cloudinary->upload($request->file('cover_image'), 'installations');
                $validated['cover_image'] = $upload['url'];
                $validated['cover_image_public_id'] = $upload['public_id'];
            }

            [$galleryImages, $galleryPublicIds] = $this->uploadGalleryImages($request, $cloudinary);
            $validated['gallery_images'] = $galleryImages;
            $validated['gallery_image_public_ids'] = $galleryPublicIds;
        } catch (\Throwable $e) {
            Log::error('Installation image upload failed on create: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withErrors([
                'cover_image' => 'Image upload failed. Please confirm Cloudinary settings and try again.',
            ])->withInput();
        }

        Installation::create($validated);

        return redirect()
            ->route('admin.installations.index')
            ->with('success', 'Installation created successfully.');
    }

    public function edit(Installation $installation)
    {
        return view('admin.installations.edit', compact('installation'));
    }

    public function update(Request $request, Installation $installation, CloudinaryImageService $cloudinary)
    {
        $validated = $this->validatePayload($request, $installation->id);
        $validated['slug'] = $this->buildUniqueSlug($validated['slug'] ?? null, $validated['title'], $installation->id);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_public'] = $request->boolean('is_public', true);

        try {
            if ($request->hasFile('cover_image')) {
                $this->destroyUploadedImage($installation->cover_image_public_id, $installation->cover_image, $cloudinary);
                $upload = $cloudinary->upload($request->file('cover_image'), 'installations');
                $validated['cover_image'] = $upload['url'];
                $validated['cover_image_public_id'] = $upload['public_id'];
            }

            $currentImages = $installation->gallery_images ?? [];
            $currentPublicIds = $installation->gallery_image_public_ids ?? [];

            $removeIndexes = collect($request->input('remove_gallery_indexes', []))
                ->map(static fn ($value) => (int) $value)
                ->filter(static fn (int $idx) => $idx >= 0)
                ->unique()
                ->sortDesc()
                ->values()
                ->all();

            foreach ($removeIndexes as $idx) {
                if (array_key_exists($idx, $currentPublicIds)) {
                    $this->destroyUploadedImage($currentPublicIds[$idx], null, $cloudinary);
                    unset($currentPublicIds[$idx]);
                }
                if (array_key_exists($idx, $currentImages)) {
                    $this->destroyUploadedImage(null, $currentImages[$idx], $cloudinary);
                    unset($currentImages[$idx]);
                }
            }

            $currentImages = array_values($currentImages);
            $currentPublicIds = array_values($currentPublicIds);

            [$newGalleryImages, $newGalleryPublicIds] = $this->uploadGalleryImages($request, $cloudinary);
            $validated['gallery_images'] = array_values(array_merge($currentImages, $newGalleryImages));
            $validated['gallery_image_public_ids'] = array_values(array_merge($currentPublicIds, $newGalleryPublicIds));
        } catch (\Throwable $e) {
            Log::error('Installation image upload failed on update: ' . $e->getMessage(), ['exception' => $e, 'installation_id' => $installation->id]);
            return back()->withErrors([
                'cover_image' => 'Image upload/update failed. Please confirm Cloudinary settings and try again.',
            ])->withInput();
        }

        $installation->update($validated);

        return redirect()
            ->route('admin.installations.index')
            ->with('success', 'Installation updated successfully.');
    }

    public function destroy(Installation $installation, CloudinaryImageService $cloudinary)
    {
        $this->destroyUploadedImage($installation->cover_image_public_id, $installation->cover_image, $cloudinary);

        foreach (($installation->gallery_image_public_ids ?? []) as $publicId) {
            $this->destroyUploadedImage($publicId, null, $cloudinary);
        }

        foreach (($installation->gallery_images ?? []) as $pathOrUrl) {
            $this->destroyUploadedImage(null, $pathOrUrl, $cloudinary);
        }

        $installation->delete();

        return redirect()
            ->route('admin.installations.index')
            ->with('success', 'Installation deleted successfully.');
    }

    private function validatePayload(Request $request, ?int $installationId = null): array
    {
        $slugRule = 'nullable|string|max:255|unique:installations,slug';
        if ($installationId !== null) {
            $slugRule .= ',' . $installationId;
        }

        return $request->validate([
            'title' => 'required|string|max:255',
            'slug' => $slugRule,
            'category' => 'required|string|max:120',
            'city' => 'required|string|max:120',
            'client_type' => 'nullable|string|max:120',
            'completed_at' => 'nullable|date',
            'summary' => 'required|string|max:1200',
            'outcome' => 'nullable|string|max:1200',
            'sort_order' => 'nullable|integer|min:0|max:999999',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
            'gallery_images' => 'nullable|array|max:20',
            'gallery_images.*' => 'image|mimes:jpeg,png,jpg,webp|max:10240',
            'is_featured' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
            'remove_gallery_indexes' => 'nullable|array',
            'remove_gallery_indexes.*' => 'integer|min:0',
        ]);
    }

    private function uploadGalleryImages(Request $request, CloudinaryImageService $cloudinary): array
    {
        $urls = [];
        $publicIds = [];

        foreach ($request->file('gallery_images', []) as $file) {
            $upload = $cloudinary->upload($file, 'installations');
            $urls[] = $upload['url'];
            $publicIds[] = $upload['public_id'];
        }

        return [$urls, $publicIds];
    }

    private function destroyUploadedImage(?string $publicId, ?string $pathOrUrl, CloudinaryImageService $cloudinary): void
    {
        if (!empty($publicId)) {
            try {
                $cloudinary->destroy($publicId);
            } catch (\Throwable $e) {
                Log::warning('Failed to delete image from Cloudinary: ' . $e->getMessage(), ['public_id' => $publicId]);
            }
            return;
        }

        if (!empty($pathOrUrl) && !ImageUrl::isAbsolute($pathOrUrl)) {
            Storage::disk('public')->delete($pathOrUrl);
        }
    }

    private function buildUniqueSlug(?string $candidate, string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug((string) ($candidate ?: $title));
        if ($base === '') {
            $base = 'installation';
        }

        $slug = $base;
        $suffix = 2;

        while ($this->slugExists($slug, $ignoreId)) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return Installation::query()
            ->when($ignoreId, static fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists();
    }
}
