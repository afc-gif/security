<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use App\Models\Installation;
use Illuminate\Support\Carbon;

// Public Website Homepage, Robots, Sitemap
Route::middleware('web')->group(function () {
    Route::get('/', function () {
        $installations = collect();

        try {
            if (Schema::hasTable('installations')) {
                $installations = Installation::query()
                    ->where('is_public', true)
                    ->orderByDesc('is_featured')
                    ->orderBy('sort_order')
                    ->orderByDesc('completed_at')
                    ->orderByDesc('id')
                    ->limit(8)
                    ->get();
            }
        } catch (\Throwable $e) {
            // Keep homepage functional even if database is temporarily unavailable.
        }

        return view('welcome', compact('installations'));
    })->name('home');
});

Route::get('/robots.txt', function () {
    $content = implode("\n", [
        'User-agent: *',
        'Allow: /',
        '',
        'Sitemap: ' . url('/sitemap.xml'),
    ]);

    return response($content, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
});

Route::get('/sitemap.xml', function () {
    $now = Carbon::now()->toAtomString();
    $urls = [
        ['loc' => url('/'), 'lastmod' => $now, 'changefreq' => 'weekly', 'priority' => '1.0'],
        ['loc' => url('/solutions'), 'lastmod' => $now, 'changefreq' => 'daily', 'priority' => '0.9'],
    ];

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    foreach ($urls as $url) {
        $xml .= "    <url>\n";
        $xml .= '        <loc>' . e($url['loc']) . "</loc>\n";
        $xml .= '        <lastmod>' . e($url['lastmod']) . "</lastmod>\n";
        $xml .= '        <changefreq>' . e($url['changefreq']) . "</changefreq>\n";
        $xml .= '        <priority>' . e($url['priority']) . "</priority>\n";
        $xml .= "    </url>\n";
    }

    $xml .= '</urlset>';

    return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
});

Route::prefix('api')->group(function () {
    Route::get('/health', fn () => ['status' => 'ok']);
});

// Admin Installations Gallery Management
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::resource('installations', \App\Http\Controllers\Website\InstallationController::class)->names([
        'index' => 'admin.installations.index',
        'create' => 'admin.installations.create',
        'store' => 'admin.installations.store',
        'edit' => 'admin.installations.edit',
        'update' => 'admin.installations.update',
        'destroy' => 'admin.installations.destroy',
    ])->except(['show']);
});
