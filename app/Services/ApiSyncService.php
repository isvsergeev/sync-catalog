<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ApiSyncService
{
    protected string $apiUrl;
    protected ?string $apiKey;
    protected ?string $apiSecret;
    protected ?string $apiCallbackUrl;

    public function __construct()
    {
        $this->apiUrl = config('services.rees46.api_url');
        $this->apiKey = config('services.rees46.api_key');
        $this->apiSecret = config('services.rees46.api_secret');
        $this->apiCallbackUrl = config('services.rees46.api_callback_url');
    }

    /**
     * Импорт категорий
     *
     * @param array $categories
     * @return void
     * https://rees46.ru/help/integration/catalog/import/api/categories.html
     */
    public function importCategories(array $categories): array
    {
        if (!$this->checkCredentials()) {
            return [
                'status' => false,
                'message' => 'Укажите параметры apiKey и apiSecret'
            ];
        }
        try {
            $data = [
                'shop_id' => $this->apiKey,
                'shop_secret' => $this->apiSecret,
                'categories' => $categories,
            ];
            if ($this->apiCallbackUrl) {
                $data['webhook'] = $this->apiCallbackUrl;
            }
            $response = Http::post("{$this->apiUrl}/categories", $data);

            return [
                'status' => true,
                'code' => $response->getStatusCode(),
                'message' => $response->body()
            ];
        } catch ( Exception $e )
        {
            Log::error('Import categories error: ', [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);

            return [
                'status' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Импорт локаций
     *
     * @param array $locations
     * @return void
     * https://rees46.ru/help/integration/catalog/import/api/locations.html
     */
    public function importLocations(array $locations): array
    {
        if (!$this->checkCredentials()) {
            return [
                'status' => false,
                'message' => 'Укажите параметры apiKey и apiSecret'
            ];
        }

        try {
            $data = [
                'shop_id' => $this->apiKey,
                'shop_secret' => $this->apiSecret,
                'locations' => $locations,
            ];
            if ($this->apiCallbackUrl) {
                $data['webhook'] = $this->apiCallbackUrl;
            }

            $response = Http::post("{$this->apiUrl}/locations", $data);

            return [
                'status' => true,
                'code' => $response->getStatusCode(),
                'message' => $response->body()
            ];
        } catch ( Exception $e )
        {
            Log::error('Import locations error: ', [
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);

            return [
                'status' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Импорт оферов
     *
     * @param array $products
     * @return void
     * https://rees46.ru/help/integration/catalog/import/api/products.html
     */
    public function importProducts(array $products): array
    {
        if (!$this->checkCredentials()) {
            return [
                'status' => false,
                'message' => 'Укажите параметры apiKey и apiSecret'
            ];
        }

        try {
            $response = Http::post("{$this->apiUrl}/products", [
                'shop_id' => $this->apiKey,
                'shop_secret' => $this->apiSecret,
                'products' => $products
            ]);
            usleep(100000);

            return [
                'status' => true,
                'code' => $response->getStatusCode(),
                'message' => $response->body()
            ];
        } catch ( Exception $e )
        {
            Log::error('Import products error: ', [
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);

            return [
                'status' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
    }

    private function checkCredentials(): bool
    {
        return $this->apiKey && $this->apiSecret;
    }
}
