<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CurrencyService
{

    private int $tries = 0;

    /**
     * @param array $params
     * @return array
     */

    public function getRates(): array
    {
        $cacheKey = 'currency_rates';
        $rates = Cache::get($cacheKey);
        if (!$rates) {
            $response = $this->getRatesApi();
            if (!$response['success']) {

                return $response;
            }
            $rates = $response['data'];
            Cache::put($cacheKey, $response['data'], 60 * 24);
        }

        return [
            'success' => true,
            'type'    => 'success',
            'data'    => $rates
        ];
    }

    private function getRatesApi()
    {
        if ($this->tries > 3) {
            return [
                'success' => false,
                'type'    => 'error',
                'message' => 'Something went wrong'
            ];
        }
        $client = new Client();
        $response = $client->get(config('currency.external_api_url'));
        $this->tries++;

        if ($response->getStatusCode() === 200) {
            try {
                $xml = $response->getBody()->getContents();
                $xmlJson = json_encode(simplexml_load_string($xml));

                return [
                    'success' => true,
                    'type'    => 'success',
                    'data'    => json_decode($xmlJson, true)['Valute']
                ];
            } catch (\Exception $exception) {
                Log::error($exception);
                $this->getRatesApi();
            }
        } else if ($this->tries <= 3) {
            dd($this->tries);
            $this->getRatesApi();
        }
    }
}
