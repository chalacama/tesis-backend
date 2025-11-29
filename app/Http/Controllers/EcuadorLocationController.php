<?php

namespace App\Http\Controllers;


use App\Services\EcuadorLocationService;
use Illuminate\Http\JsonResponse;

class EcuadorLocationController extends Controller
{
    public function provinces(EcuadorLocationService $locations): JsonResponse
    {
        return response()->json([
            'data' => $locations->getProvinces(),
        ]);
    }

    public function cantons(EcuadorLocationService $locations, string $provinceId): JsonResponse
    {
        return response()->json([
            'data' => $locations->getCantons($provinceId),
        ]);
    }

    public function parishes(
        EcuadorLocationService $locations,
        string $provinceId,
        string $cantonId
    ): JsonResponse {
        return response()->json([
            'data' => $locations->getParishes($provinceId, $cantonId),
        ]);
    }
}
