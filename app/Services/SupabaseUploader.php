<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class SupabaseUploader {
    private $baseUrl;
    private $bucketName;
    private $apiKey;
    private $client;

    use ApiResponse;

    public function __construct() {
        $this->baseUrl = config('services.supabase.url') . '/storage/v1';
        $this->bucketName = config('services.supabase.bucket');
        $this->apiKey = config('services.supabase.key');
        $this->client = new Client([
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function uploadFile($file, $filePath) {
        try {
            $response = $this->client->post($this->baseUrl . '/object/' . $this->bucketName . '/' . $filePath, [
                'headers' => [
                    'Content-Type' => $file->getClientMimeType(),
                ],
                'body' => file_get_contents($file->getRealPath()),
            ]);

            if ($response->getStatusCode() === 200 || $response->getStatusCode() === 201) {
                return json_decode($response->getBody(), true);
            }

            throw new \Exception("Upload failed: " . $response->getBody());
        } catch (Exception $e) {
            Log::error('Supabase upload error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getPublicUrl($filePath) {
        return $this->baseUrl . '/object/public/' . $this->bucketName . '/' . $filePath;
    }

    public function getBasePublicUrl() {
        return $this->baseUrl . '/object/public/' . $this->bucketName . '/';
    }

    public function deleteFile($filePath) {
        try {
            $response = $this->client->delete($this->baseUrl . '/object/' . $this->bucketName . '/' . $filePath, [
                'body' => json_encode(['fileName' => $filePath]),
            ]);

            if ($response->getStatusCode() === 200 || $response->getStatusCode() === 204) {
                return json_decode($response->getBody(), true);
            }

            throw new \Exception("Delete failed: " . $response->getBody());
        } catch (Exception $e) {
            Log::error('Supabase delete error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateFile($file, $filePath) {
        try {
            $response = $this->client->put($this->baseUrl . '/object/' . $this->bucketName . '/' . $filePath, [
                'headers' => [
                    'Content-Type' => $file->getClientMimeType(),
                ],
                'body' => file_get_contents($file->getRealPath()),
            ]);

            if ($response->getStatusCode() === 200 || $response->getStatusCode() === 201) {
                return json_decode($response->getBody(), true);
            }

            throw new \Exception("Update failed: " . $response->getBody());
        } catch (Exception $e) {
            Log::error('Supabase update error: ' . $e->getMessage());
            throw $e;
        }
    }
}
