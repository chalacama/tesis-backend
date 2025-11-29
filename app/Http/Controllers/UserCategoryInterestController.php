<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use App\Models\UserCategoryInterest;
use App\Models\Category;

class UserCategoryInterestController extends Controller
{
    use AuthorizesRequests;

    /**
     * Mostrar las categorías seleccionadas por el usuario autenticado.
     *
     * GET /api/category/show
     */
    public function show(): JsonResponse
    {
        $user = Auth::user();

        // Traemos los intereses con la relación category
        $interests = UserCategoryInterest::with('category')
            ->where('user_id', $user->id)
            ->get();

        // Mapeamos solo los datos de la categoría
        $categories = $interests
            ->filter(fn ($interest) => $interest->category !== null)
            ->map(function ($interest) {
                return [
                    'id'   => $interest->category->id,
                    'name' => $interest->category->name,
                ];
            })
            ->values();

        return response()->json([
            'ok'      => true,
            'message' => 'Categorías de interés del usuario obtenidas correctamente.',
            'data'    => [
                'user_id'   => $user->id,
                'categories'=> $categories,
            ],
        ]);
    }

    /**
     * Actualizar las categorías de interés del usuario autenticado.
     *
     * PUT /api/category/update
     *
     * Body JSON de ejemplo:
     * {
     *   "categories": [1, 2, 3, 4]
     * }
     */
    public function update(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Política sobre el usuario (UserPolicy@update o similar)
        $this->authorize('update', $user);

        // Validación: exactamente 4 categorias, sin duplicados, que existan en categories.id
        $validated = $request->validate([
            'categories'   => ['required', 'array', 'size:4'  ],
            'categories.*' => ['integer', 'distinct', 'exists:categories,id'],
        ]);

        $categoryIds = $validated['categories'];

        DB::transaction(function () use ($user, $categoryIds) {
            // Borrar intereses anteriores
            UserCategoryInterest::where('user_id', $user->id)->delete();

            // Insertar los nuevos intereses
            $rows = [];
            foreach ($categoryIds as $categoryId) {
                $rows[] = [
                    'user_id'     => $user->id,
                    'category_id' => $categoryId,
                ];
            }

            UserCategoryInterest::insert($rows);
        });

        // Traer info de las categorías recién guardadas
        $categories = Category::whereIn('id', $categoryIds)
            ->get(['id', 'name']);

        return response()->json([
            'ok'      => true,
            'message' => 'Categorías de interés actualizadas correctamente.',
            'data'    => [
                'user_id'   => $user->id,
                'categories'=> $categories,
            ],
        ]);
    }
}
