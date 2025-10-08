<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\TypeLearningContent;
class TypeLearningContentController extends Controller
{
    
    public function index(): JsonResponse
    {
        
        return response()->json(TypeLearningContent::all());
    }
}
