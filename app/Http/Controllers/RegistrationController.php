<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Course;
use App\Models\Registration;
use App\Models\User;
class RegistrationController extends Controller
{
    

public function registerUserToCourse(Request $request)
{
    $request->validate([
        'user_id' => 'required|integer',
        'course_id' => 'required|integer',
    ]);

    $userId = $request->input('user_id');
    $courseId = $request->input('course_id');

    // Verificar si el usuario y el curso existen
    $user = User::find($userId);
    $course = Course::find($courseId);

    if (!$course || !$course->enabled) {
    return response()->json(['error' => 'El curso no está activo o no existe'], 400);
    }

    // Verificar si el usuario ya está registrado en el curso
    $registration = Registration::where('user_id', $userId)->where('course_id', $courseId)->first();

    if ($registration) {
        return response()->json(['error' => 'Usuario ya registrado en el curso'], 400);
    }

    // Registrar al usuario en el curso
    $registration = new Registration();
    $registration->user_id = $userId;
    $registration->course_id = $courseId;
    $registration->save();

    return response()->json(['message' => 'Usuario registrado en el curso con éxito']);
}
public function cancelRegistrationUserToCourse(Request $request)
{
    $request->validate([
        'user_id' => 'required|integer',
        'course_id' => 'required|integer',
    ]);

    $userId = $request->input('user_id');
    $courseId = $request->input('course_id');

    // Verificar si el usuario y el curso existen
    $user = User::find($userId);
    $course = Course::find($courseId);

    if (!$course || !$course->enabled) {
        return response()->json(['error' => 'El curso no está activo o no existe'], 400);
    }

    // Verificar si el usuario está inscrito en el curso
    $registration = Registration::where('user_id', $userId)->where('course_id', $courseId)->first();

    if (!$registration) {
        return response()->json(['error' => 'El usuario no está inscrito en el curso'], 400);
    }
    // Verificar si la inscripción ya está cancelada
    if ($registration->annulment) {
        return response()->json(['error' => 'La inscripción ya estaba cancelada'], 400);
    }

    // Cancelar la inscripción
    $registration->annulment = true;
    $registration->save();

    return response()->json(['message' => 'Inscripción cancelada con éxito']);
}
}
