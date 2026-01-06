<?php

namespace App\Services;

use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\BarcodeGeneratorHTML;
use Picqer\Barcode\BarcodeGeneratorSVG;

class BarcodeGenerator
{
    /**
     * Generate barcode in requested format
     */
    public static function generateImage(string $barcode, string $format = 'png'): string
    {
        return match ($format) {
            'png' => self::generatePNG($barcode),
            'svg' => self::generateSVG($barcode),
            'html' => self::generateHTML($barcode),
            'print' => self::generatePrintHTML($barcode),
            default => self::generatePNG($barcode),
        };
    }

    /**
     * Generate high-quality PNG barcode for scanning
     */
    private static function generatePNG(string $barcode): string
    {
        try {
            $generator = new BarcodeGeneratorPNG();
            return $generator->getBarcode($barcode, BarcodeGeneratorPNG::TYPE_CODE_128);
        } catch (\Exception $e) {
            return self::generateHTML($barcode);
        }
    }

    /**
     * Generate SVG barcode (vector format)
     */
    private static function generateSVG(string $barcode): string
    {
        try {
            $generator = new BarcodeGeneratorSVG();
            return $generator->getBarcode($barcode, BarcodeGeneratorSVG::TYPE_CODE_128);
        } catch (\Exception $e) {
            return '<svg></svg>';
        }
    }

    /**
     * Generate HTML barcode representation
     */
    private static function generateHTML(string $barcode): string
    {
        try {
            $generator = new BarcodeGeneratorHTML();
            return $generator->getBarcode($barcode, BarcodeGeneratorHTML::TYPE_CODE_128);
        } catch (\Exception $e) {
            return htmlspecialchars($barcode);
        }
    }

    /**
     * Generate printable barcode HTML page with label
     */
    private static function generatePrintHTML(string $barcode): string
    {
        try {
            $generator = new BarcodeGeneratorPNG();
            $imageData = $generator->getBarcode($barcode, BarcodeGeneratorPNG::TYPE_CODE_128);
            $base64Image = 'data:image/png;base64,' . base64_encode($imageData);
        } catch (\Exception $e) {
            $base64Image = '';
        }

        $timestamp = now()->format('Y-m-d H:i:s');
        $barcodeEscaped = htmlspecialchars($barcode);

        $html = <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcode Label</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', monospace;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .label {
            background: white;
            border: 2px solid #333;
            padding: 40px;
            text-align: center;
            max-width: 600px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .label h2 {
            margin-bottom: 30px;
            font-size: 16px;
            color: #333;
            font-weight: bold;
        }
        .barcode-container {
            margin: 30px 0;
            background: #f0f0f0;
            padding: 30px;
            border: 2px solid #333;
            border-radius: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .barcode-image {
            max-width: 100%;
            height: auto;
            border: 1px solid #999;
            padding: 10px;
            background: white;
        }
        .barcode-value {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 2px;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
            color: #000;
            word-break: break-all;
        }
        .info {
            margin-top: 20px;
            font-size: 11px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        @media print {
            body {
                padding: 0;
                background: white;
            }
            .label {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="label">
        <h2>Product Barcode Label</h2>
        
        <div class="barcode-container">
            <img src="$base64Image" alt="Barcode" class="barcode-image">
        </div>
        
        <div class="barcode-value">$barcodeEscaped</div>
        
        <div class="info">
            <p><strong>Format:</strong> CODE_128</p>
            <p><strong>Generated:</strong> $timestamp</p>
            <p>Print and attach to product. Compatible with barcode scanners.</p>
        </div>
    </div>
</body>
</html>
EOT;

        return $html;
    }

    /**
     * Get barcode metadata
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
