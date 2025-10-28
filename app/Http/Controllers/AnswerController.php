<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chapter;
class AnswerController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request , Chapter $chapter)
    {
        
        $course = $chapter->module->course;
        $this->authorize('view', $course);

        if (!$course->enabled) {
            return response()->json(['ok' => false, 'message' => 'El curso no está activo.'], 403);
        }

        

    }
}
