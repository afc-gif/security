<?php
// Simple test to verify Cloudinary setup

require_once __DIR__ . '/backend/vendor/autoload.php';
require_once __DIR__ . '/backend/bootstrap/app.php';

use App\Services\CloudinaryImageService;
use Illuminate\Support\Facades\Config;

// Test configuration loading
echo "=== Cloudinary Configuration ===\n";
echo "Cloud Name: " . Config::get('services.cloudinary.cloud_name') . "\n";
echo "API Key: " . (Config::get('services.cloudinary.api_key') ? '✓ Set' : '✗ NOT SET') . "\n";
echo "API Secret: " . (Config::get('services.cloudinary.api_secret') ? '✓ Set' : '✗ NOT SET') . "\n";
echo "Root Folder: " . Config::get('services.cloudinary.root_folder') . "\n";

// Test if credentials are missing
if (!Config::get('services.cloudinary.cloud_name') || 
    !Config::get('services.cloudinary.api_key') || 
    !Config::get('services.cloudinary.api_secret')) {
    echo "\n❌ ERROR: Cloudinary credentials are missing or incomplete!\n";
    exit(1);
}

echo "\n✓ Cloudinary credentials are properly configured!\n";

// Test products table
echo "\n=== Products in Database ===\n";
$products = \App\Models\Product::all();
echo "Total Products: " . count($products) . "\n\n";

foreach ($products as $product) {
    echo "ID: {$product->id}\n";
    echo "Name: {$product->name}\n";
    echo "Image: " . ($product->image ? '✓ Set' : '✗ NOT SET') . "\n";
    echo "Image Public ID: " . ($product->image_public_id ? '✓ Set' : '✗ NOT SET') . "\n";
    echo "---\n";
}

if (count($products) === 0) {
    echo "No products found. Create a new product to test Cloudinary upload.\n";
}
