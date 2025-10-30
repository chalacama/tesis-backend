<?php

namespace App\Http\Controllers;

use App\Models\Test;
use Illuminate\Http\Request;
use App\Models\Chapter;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class TestController extends Controller
{
     use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
     public function index(Request $request, Chapter $chapter)
    {
        
        $this->authorize('viewChapter', $chapter);

        
        return response()->json([
            'ok' => true,
            // 'data' => ...
        ]);
    }

}
