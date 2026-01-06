<?php

namespace App\Services;

use Picqer\Barcode\BarcodeGenerator as PicqerBarcodeGenerator;
use Picqer\Barcode\BarcodeGeneratorPNG;

class BarcodeGenerator
{
    /**
     * Generate a barcode image (PNG) from barcode string
     *
     * @param string $barcode The barcode data (typically alphanumeric)
     * @param string $format Output format: 'png', 'svg', 'html' (default: 'png')
     * @return string Binary image data (PNG) or SVG/HTML string
     */
    public static function generateImage(string $barcode, string $format = 'png'): string
    {
        return match ($format) {
            'png' => self::generatePNG($barcode),
            'svg' => self::generateSVG($barcode),
            'html' => self::generateHTML($barcode),
            default => self::generatePNG($barcode),
        };
    }

    /**
     * Generate PNG barcode image
     */
    private static function generatePNG(string $barcode): string
    {
        $generator = new BarcodeGeneratorPNG();
        return $generator->getBarcode($barcode, BarcodeGeneratorPNG::TYPE_CODE_128);
    }

    /**
     * Generate SVG barcode image
     */
    private static function generateSVG(string $barcode): string
    {
        $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
        return $generator->getBarcode($barcode, \Picqer\Barcode\BarcodeGeneratorSVG::TYPE_CODE_128);
    }

    /**
     * Generate HTML barcode (useful for preview)
     */
    private static function generateHTML(string $barcode): string
    {
        $generator = new \Picqer\Barcode\BarcodeGeneratorHTML();
        return $generator->getBarcode($barcode, \Picqer\Barcode\BarcodeGeneratorHTML::TYPE_CODE_128);
    }

    /**
     * Get barcode metadata for display
     */
    public static function getMetadata(string $barcode): array
    {
        return [
            'barcode' => $barcode,
            'length' => strlen($barcode),
            'type' => 'CODE_128',
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
