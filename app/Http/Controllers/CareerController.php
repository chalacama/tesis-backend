<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Career;
class CareerController extends Controller
{
    public function index()
    {
        $careers = Career::orderBy('name', 'asc')->get();

        return response()->json([
                'success' => true,
                'message' => 'Lista de carreras obtenida correctamente.',
                'data'    => $careers
        ], 200);
    }
}
