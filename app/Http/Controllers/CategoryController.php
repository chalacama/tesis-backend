<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
class CategoryController extends Controller
{
    public function index()
    {
        // Obtiene todas las categorías ordenadas por ID descendente
        $categories = Category::orderBy('name', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Lista de categorías obtenida correctamente',
            'data' => $categories
        ], 200);
    }
}
