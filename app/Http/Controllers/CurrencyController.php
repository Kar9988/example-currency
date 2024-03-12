<?php

namespace App\Http\Controllers;

use App\Services\CurrencyService;
use Illuminate\Http\JsonResponse;

class CurrencyController extends Controller
{
    private CurrencyService $currencyService;

    /**
     * @param CurrencyService $currencyService
     */
    public function __construct(CurrencyService $currencyService)
    {

        $this->currencyService = $currencyService;
    }

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $data = $this->currencyService->getRates();

        return response()->json($data);
    }
}
