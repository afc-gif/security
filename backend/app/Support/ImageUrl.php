<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUrl
{
    public static function url(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (self::isAbsolute($value)) {
            return $value;
        }

        return Storage::disk('public')->url($value);
    }

    public static function isAbsolute(?string $value): bool
    {
        if (empty($value)) {
            return false;
        }

        return Str::startsWith($value, ['http://', 'https://']);
    }
}
