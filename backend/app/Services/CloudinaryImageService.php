<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CloudinaryImageService
{
    public function upload(UploadedFile $file, string $subFolder): array
    {
        $cloudName = (string) config('services.cloudinary.cloud_name');
        $apiKey = (string) config('services.cloudinary.api_key');
        $apiSecret = (string) config('services.cloudinary.api_secret');
        $rootFolder = trim((string) config('services.cloudinary.root_folder', 'security'), '/');

        if ($cloudName === '' || $apiKey === '' || $apiSecret === '') {
            throw new RuntimeException('Cloudinary credentials are missing.');
        }

        $folder = trim($rootFolder . '/' . trim($subFolder, '/'), '/');
        $timestamp = time();
        $signature = $this->sign([
            'folder' => $folder,
            'timestamp' => $timestamp,
        ], $apiSecret);

        $endpoint = "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload";

        $response = Http::asMultipart()
            ->timeout(45)
            ->post($endpoint, [
                [
                    'name' => 'file',
                    'contents' => fopen($file->getRealPath(), 'r'),
                    'filename' => $file->getClientOriginalName(),
                ],
                ['name' => 'api_key', 'contents' => $apiKey],
                ['name' => 'timestamp', 'contents' => (string) $timestamp],
                ['name' => 'folder', 'contents' => $folder],
                ['name' => 'signature', 'contents' => $signature],
            ]);

        try {
            $response->throw();
        } catch (RequestException $e) {
            throw new RuntimeException('Cloudinary upload failed: ' . $e->getMessage(), 0, $e);
        }

        $payload = $response->json();

        if (!is_array($payload) || empty($payload['secure_url']) || empty($payload['public_id'])) {
            throw new RuntimeException('Cloudinary upload failed: invalid response payload.');
        }

        return [
            'url' => $payload['secure_url'],
            'public_id' => $payload['public_id'],
        ];
    }

    public function destroy(?string $publicId): void
    {
        if (empty($publicId)) {
            return;
        }

        $cloudName = (string) config('services.cloudinary.cloud_name');
        $apiKey = (string) config('services.cloudinary.api_key');
        $apiSecret = (string) config('services.cloudinary.api_secret');

        if ($cloudName === '' || $apiKey === '' || $apiSecret === '') {
            return;
        }

        $timestamp = time();
        $signature = $this->sign([
            'public_id' => $publicId,
            'timestamp' => $timestamp,
        ], $apiSecret);

        $endpoint = "https://api.cloudinary.com/v1_1/{$cloudName}/image/destroy";

        $response = Http::asForm()
            ->timeout(30)
            ->post($endpoint, [
                'public_id' => $publicId,
                'timestamp' => $timestamp,
                'api_key' => $apiKey,
                'signature' => $signature,
            ]);

        $response->throw();
    }

    private function sign(array $params, string $apiSecret): string
    {
        ksort($params);
        $parts = [];
        foreach ($params as $key => $value) {
            $parts[] = $key . '=' . $value;
        }

        return sha1(implode('&', $parts) . $apiSecret);
    }
}
