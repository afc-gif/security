<?php

namespace App\Http\Controllers;

use App\Models\SolutionItem;
use App\Services\BarcodeGenerator;
use Illuminate\Http\Response;

class BarcodeController extends Controller
{
    /**
     * Download barcode image for a solution item
     */
    public function download(SolutionItem $solutionItem)
    {
        if (!$solutionItem->barcode) {
            abort(404, 'Barcode not found for this item');
        }

        // Generate barcode image
        $barcodeImage = BarcodeGenerator::generateImage($solutionItem->barcode, 'png');

        // Return as downloadable image
        return response($barcodeImage)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', "attachment; filename=\"{$solutionItem->barcode}.png\"");
    }

    /**
     * Display barcode image inline (for viewing)
     */
    public function view(SolutionItem $solutionItem)
    {
        if (!$solutionItem->barcode) {
            abort(404, 'Barcode not found for this item');
        }

        // Generate barcode image
        $barcodeImage = BarcodeGenerator::generateImage($solutionItem->barcode, 'png');

        // Return as displayable image
        return response($barcodeImage)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * Get barcode as SVG (vector format)
     */
    public function svg(SolutionItem $solutionItem)
    {
        if (!$solutionItem->barcode) {
            abort(404, 'Barcode not found for this item');
        }

        // Generate barcode SVG
        $barcodeSVG = BarcodeGenerator::generateImage($solutionItem->barcode, 'svg');

        // Return as SVG
        return response($barcodeSVG)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', "attachment; filename=\"{$solutionItem->barcode}.svg\"");
    }
}
