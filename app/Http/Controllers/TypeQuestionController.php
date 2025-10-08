<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TypeQuestion;
use Illuminate\Http\JsonResponse;
class TypeQuestionController extends Controller
{

    public function index(): JsonResponse
    {
        
        return response()->json(TypeQuestion::all());
    }
}
