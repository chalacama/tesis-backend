<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Difficulty;
use Illuminate\Http\JsonResponse;
class DifficultyController extends Controller
{
    public function index(): JsonResponse
    {
        $difficulties = Difficulty::orderBy('id')->get();

        return response()->json([
            'success' => true,
            'message' => 'Dificultades encontradas',
            'data' => $difficulties,
        ]);
    }
}
