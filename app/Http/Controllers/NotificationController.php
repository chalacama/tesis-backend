<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Listar notificaciones del usuario autenticado.
     * Soporta:
     *  - ?unread_only=1   -> solo no leídas
     *  - ?per_page=20     -> paginación
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $perPage = (int) $request->integer('per_page', 20);
        $perPage = max(5, min($perPage, 50));

        $query = $user->notifications()->orderByDesc('created_at');

        if ($request->boolean('unread_only')) {
            $query->whereNull('read_at');
        }

        $paginator = $query->paginate($perPage);

        return response()->json([
            'ok'   => true,
            'data' => $paginator->items(), // array de notificaciones

            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
        ]);
    }

    /**
     * Devolver solo el número de notificaciones no leídas.
     * Útil para el badge de la campanita.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();

        $count = $user->unreadNotifications()->count();

        return response()->json([
            'ok'    => true,
            'count' => $count,
        ]);
    }

    /**
     * Marcar UNA notificación como leída.
     */
    public function markAsRead(string $id, Request $request): JsonResponse
    {
        $user = $request->user();

        $notification = $user->notifications()->where('id', $id)->firstOrFail();

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return response()->json([
            'ok' => true,
            'message' => 'Notificación marcada como leída.',
        ]);
    }

    /**
     * Marcar TODAS las notificaciones no leídas como leídas.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->unreadNotifications->markAsRead();

        return response()->json([
            'ok' => true,
            'message' => 'Todas las notificaciones fueron marcadas como leídas.',
        ]);
    }
}
