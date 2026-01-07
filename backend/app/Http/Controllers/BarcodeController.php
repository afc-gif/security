<?php

namespace App\Http\Controllers;

use App\Models\SolutionItem;
use App\Services\BarcodeGenerator;
use Illuminate\Http\Response;

class BarcodeController extends Controller
{
    /**
     * Display download page with barcode preview
     */
    public function download(SolutionItem $solutionItem)
    {
        if (!$solutionItem->barcode) {
            abort(404, 'Barcode not found for this item');
        }

        // Generate SVG barcode for preview
        $barcodeSvg = BarcodeGenerator::generateImage(
            $solutionItem->barcode,
            'svg'
        );

        return view('barcode.download', [
            'barcode' => $solutionItem->barcode,
            'barcodeSvg' => $barcodeSvg,
            'solutionItem' => $solutionItem
        ]);
    }

    /**
     * Download barcode image as PNG
     */
    public function downloadImage(SolutionItem $solutionItem)
    {
        if (!$solutionItem->barcode) {
            abort(404, 'Barcode not found for this item');
        }

        // Generate PNG barcode
        $barcodeImage = BarcodeGenerator::generateImage(
            $solutionItem->barcode,
            'png'
        );

        // Return as downloadable image
        return response($barcodeImage)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', "attachment; filename=\"{$solutionItem->barcode}.png\"")
            ->header('Cache-Control', 'public, max-age=86400');
    }

    /**
     * Display barcode image inline (for viewing)
     */
    public function view(SolutionItem $solutionItem)
    {
        if (!$solutionItem->barcode) {
            abort(404, 'Barcode not found for this item');
        }

        // Generate large, scannable barcode
        $barcodeImage = BarcodeGenerator::generateImage(
            $solutionItem->barcode,
            'png',
            400,
            150
        );

        // Return as displayable image
        return response($barcodeImage)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * Get barcode as SVG (vector format, can be scaled infinitely)
     */
    public function svg(SolutionItem $solutionItem)
    {
        if (!$solutionItem->barcode) {
            abort(404, 'Barcode not found for this item');
        }

        // Generate barcode SVG with large dimensions
        $barcodeSVG = BarcodeGenerator::generateImage(
            $solutionItem->barcode,
            'svg',
            600,  // larger for SVG
            200
        );

        // Return as SVG download
        return response($barcodeSVG)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', "attachment; filename=\"{$solutionItem->barcode}.svg\"")
            ->header('Cache-Control', 'public, max-age=86400');
    }

    /**
     * Get print-friendly page with barcode and number only
     * Perfect for printing and pasting on products
     */
    public function printLabel(SolutionItem $solutionItem)
    {
        if (!$solutionItem->barcode) {
            abort(404, 'Barcode not found for this item');
        }

        // Generate SVG barcode
        $barcodeSvg = BarcodeGenerator::generateImage(
            $solutionItem->barcode,
            'svg'
        );

        return view('barcode.print', [
            'barcode' => $solutionItem->barcode,
            'barcodeSvg' => $barcodeSvg
        ]);
    }

    /**
     * Get barcode metadata (width, height recommendations, etc.)
     */
    public function metadata(SolutionItem $solutionItem)
    {
        if (!$solutionItem->barcode) {
            abort(404, 'Barcode not found for this item');
        }

        $metadata = BarcodeGenerator::getMetadata($solutionItem->barcode);

        return response()->json([
            'success' => true,
            'barcode' => $solutionItem->barcode,
            'item_id' => $solutionItem->id,
            'item_name' => $solutionItem->name,
            'item_price' => $solutionItem->price,
            'metadata' => $metadata,
            'download_url' => route('barcode.download', ['solutionItem' => $solutionItem->id]),
            'print_url' => route('barcode.print', ['solutionItem' => $solutionItem->id]),
            'svg_url' => route('barcode.svg', ['solutionItem' => $solutionItem->id]),
        ]);
    }
}
