<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use RuntimeException;

class CloudinaryImageService
{
    public function upload(UploadedFile $file, string $subFolder): array
    {
        return $this->uploadWithResourceType($file, $subFolder, 'image');
    }

    public function uploadMedia(UploadedFile $file, string $subFolder): array
    {
        return $this->uploadWithResourceType($file, $subFolder, 'auto');
    }

    private function uploadWithResourceType(UploadedFile $file, string $subFolder, string $resourceType): array
    {
        [$cloudName, $apiKey, $apiSecret, $rootFolder] = $this->credentials();

        if ($cloudName === '' || $apiKey === '' || $apiSecret === '') {
            throw new RuntimeException('Cloudinary credentials are missing.');
        }

        $folder = trim($rootFolder . '/' . trim($subFolder, '/'), '/');
        $timestamp = time();
        $signature = $this->sign([
            'folder' => $folder,
            'timestamp' => $timestamp,
        ], $apiSecret);

        $endpoint = "https://api.cloudinary.com/v1_1/{$cloudName}/{$resourceType}/upload";

        $payload = $this->postMultipart($endpoint, [
            'file' => new \CURLFile($file->getRealPath(), $file->getMimeType() ?: 'application/octet-stream', $file->getClientOriginalName()),
            'api_key' => $apiKey,
            'timestamp' => (string) $timestamp,
            'folder' => $folder,
            'signature' => $signature,
        ]);

        if (!is_array($payload) || empty($payload['secure_url']) || empty($payload['public_id'])) {
            throw new RuntimeException('Cloudinary upload failed: invalid response payload.');
        }

        return [
            'url' => $payload['secure_url'],
            'public_id' => $payload['public_id'],
            'resource_type' => $payload['resource_type'] ?? $resourceType,
        ];
    }

    public function destroy(?string $publicId): void
    {
        if (empty($publicId)) {
            return;
        }

        [$cloudName, $apiKey, $apiSecret] = $this->credentials();

        if ($cloudName === '' || $apiKey === '' || $apiSecret === '') {
            return;
        }

        $timestamp = time();
        $signature = $this->sign([
            'public_id' => $publicId,
            'timestamp' => $timestamp,
        ], $apiSecret);

        $endpoint = "https://api.cloudinary.com/v1_1/{$cloudName}/image/destroy";

        $this->postForm($endpoint, [
            'public_id' => $publicId,
            'timestamp' => (string) $timestamp,
            'api_key' => $apiKey,
            'signature' => $signature,
        ]);
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

    private function credentials(): array
    {
        $cloudName = $this->value('services.cloudinary.cloud_name', 'CLOUDINARY_CLOUD_NAME');
        $apiKey = $this->value('services.cloudinary.api_key', 'CLOUDINARY_API_KEY');
        $apiSecret = $this->value('services.cloudinary.api_secret', 'CLOUDINARY_API_SECRET');
        $rootFolder = trim($this->value('services.cloudinary.root_folder', 'CLOUDINARY_ROOT_FOLDER', 'security'), '/');

        return [$cloudName, $apiKey, $apiSecret, $rootFolder];
    }

    private function value(string $configKey, string $envKey, string $default = ''): string
    {
        $value = config($configKey);
        if (is_string($value) && $value !== '') {
            return $value;
        }

        $fromGetEnv = getenv($envKey);
        if (is_string($fromGetEnv) && $fromGetEnv !== '') {
            return $fromGetEnv;
        }

        $fromEnv = $_ENV[$envKey] ?? null;
        if (is_string($fromEnv) && $fromEnv !== '') {
            return $fromEnv;
        }

        $fromServer = $_SERVER[$envKey] ?? null;
        if (is_string($fromServer) && $fromServer !== '') {
            return $fromServer;
        }

        return $default;
    }

    private function postMultipart(string $url, array $fields): array
    {
        $ch = curl_init($url);
        if ($ch === false) {
            throw new RuntimeException('Cloudinary upload failed: unable to initialize request.');
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $fields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 45,
        ]);

        $raw = curl_exec($ch);
        if ($raw === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('Cloudinary upload failed: ' . $error);
        }

        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($raw, true);
        if ($status < 200 || $status >= 300) {
            $message = is_array($data) ? ($data['error']['message'] ?? 'unknown error') : 'unknown error';
            throw new RuntimeException('Cloudinary upload failed: ' . $message);
        }

        if (!is_array($data)) {
            throw new RuntimeException('Cloudinary upload failed: invalid response format.');
        }

        return $data;
    }

    private function postForm(string $url, array $fields): void
    {
        $ch = curl_init($url);
        if ($ch === false) {
            throw new RuntimeException('Cloudinary delete failed: unable to initialize request.');
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($fields),
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $raw = curl_exec($ch);
        if ($raw === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('Cloudinary delete failed: ' . $error);
        }

        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status < 200 || $status >= 300) {
            $data = json_decode($raw, true);
            $message = is_array($data) ? ($data['error']['message'] ?? 'unknown error') : 'unknown error';
            throw new RuntimeException('Cloudinary delete failed: ' . $message);
        }
    }
}
