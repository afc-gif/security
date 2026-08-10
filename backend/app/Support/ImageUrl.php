<?php

namespace App\Support;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUrl
{
    /**
     * Get the proper image URL, handling both Cloudinary and local storage paths
     * Returns null for missing files (frontend should use placeholder)
     */
    public static function url(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // If it's already an absolute URL (HTTP/HTTPS from Cloudinary), return as-is
        if (self::isAbsolute($value)) {
            return $value;
        }

        // For local paths, check if file exists
        // If it doesn't exist, return null to trigger placeholder image in frontend
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        if ($disk->exists($value)) {
            return $disk->url($value);
        }

        // File not found - return null instead of broken 404 link
        return null;
    }

    public static function isAbsolute(?string $value): bool
    {
        if (empty($value)) {
            return false;
        }

        return Str::startsWith($value, ['http://', 'https://']);
    }
}
