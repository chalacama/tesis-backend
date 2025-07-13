<?php

namespace App\Http\Controllers;

use App\Models\CourseInvitation;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail; // Para enviar correos
use App\Mail\TutorInvitationEmail; // Crearemos esto en el siguiente paso
use App\Notifications\TutorInvitationNotification; // Y esto también
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
class CourseInvitationController extends Controller
{
    use AuthorizesRequests;
    
   public function store(Request $request, Course $course)
{
    // Verificar autorización usando el Policy
    $this->authorize('inviteCollaborator', $course);

    // Validación: Asegurarnos de que nos envían un email válido
    $request->validate([
        'email' => 'required|email'
    ]);

    $invitedEmail = $request->input('email');

    // Lógica: Crear la invitación
    $invitation = $course->invitations()->create([
        'inviter_id' => $request->user()->id,
        'email' => $invitedEmail,
        'token' => Str::random(40) . time(),
    ]);

    // Enviar el correo electrónico de invitación
    Mail::to($invitedEmail)->send(new TutorInvitationEmail($invitation));

    // (Opcional pero recomendado) Enviar notificación DENTRO de la app si el usuario ya existe
    $invitedUser = User::where('email', $invitedEmail)->first();
    if ($invitedUser) {
        $invitedUser->notify(new TutorInvitationNotification($invitation));
    }

    return response()->json([
        'message' => 'Invitación enviada correctamente.',
        'invitation' => $invitation
    ], 201);
}
public function accept(Request $request)
{
    $request->validate(['token' => 'required|string']);

    // 1. Buscar la invitación (sin cambios)
    $invitation = CourseInvitation::where('token', $request->token)->first();

    if (!$invitation) {
        return response()->json(['message' => 'El token de invitación no es válido.'], 404);
    }
    if ($invitation->status !== 'pending') {
        return response()->json(['message' => 'Esta invitación ya ha sido procesada.'], 422);
    }

    // 2. Revisar si el usuario existe (sin cambios)
    $user = User::where('email', $invitation->email)->first();

    // 3. Manejar el caso del usuario nuevo (sin cambios)
    if (!$user) {
        return response()->json([
            'status' => 'user_not_found',
            'message' => 'No existe un usuario con este email. Por favor, regístrate primero.',
            'email' => $invitation->email,
        ], 200);
    }

    // --- AQUÍ EMPIEZA LA LÓGICA DE AUTORIZACIÓN MEJORADA ---

    // 4. Si el usuario existe, ¿está autenticado?
    if (!auth()->check()) {
        return response()->json([
            'status' => 'authentication_required',
            'message' => 'El usuario ya existe. Por favor, inicia sesión para aceptar la invitación.'
        ], 401); // 401 Unauthorized
    }

    // 5. ¡AHORA SÍ! Autorizar usando el Policy
    // Ya que el usuario sí existe y está autenticado, verificamos que sea el correcto.
    $this->authorize('accept', $invitation);

    // 6. Ejecutar la transacción (sin cambios)
    DB::transaction(function () use ($user, $invitation) {
        $invitation->course->tutors()->attach($user->id, ['is_owner' => false]);
        $invitation->update(['status' => 'accepted']);
    });

    return response()->json(['message' => '¡Felicidades! Te has unido al curso exitosamente.'], 200);
}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    /* public function store(Request $request)
    {
        //
    } */

    /**
     * Display the specified resource.
     */
    public function show(CourseInvitation $courseInvitation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CourseInvitation $courseInvitation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CourseInvitation $courseInvitation)
    {
        //
    }
}
