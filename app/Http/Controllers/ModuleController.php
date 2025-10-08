<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Course;
use App\Models\Chapter;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
class ModuleController extends Controller
{
use AuthorizesRequests;

public function index(Course $course): JsonResponse
{
    // Autorización por curso (dueño/colaborador, según tu policy)
    $this->authorize('viewHidden', $course);

    $modules = $course->modules()
        ->select('id','name','order','course_id','created_at','updated_at')
        ->with([
            'chapters' => function ($q) {
                $q->select('id','title','description','order','module_id','created_at','updated_at')
                  ->withCount('questions') // => questions_count
                  ->with([
                      'learningContent' => function ($c) {
                          $c->select('id','type_content_id','chapter_id')
                            ->with([
                                'typeLearningContent:id,name' // solo el nombre del tipo
                            ]);
                      }
                  ])
                  ->orderBy('order');
            },
        ])
        ->orderBy('order')
        ->get();

    return response()->json($modules);
}


public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'course_id' => ['required','integer','exists:courses,id'],

            'remove_missing_modules'  => ['sometimes','boolean'],
            'remove_missing_chapters' => ['sometimes','boolean'],

            'modules'                 => ['required','array'],
            'modules.*.id'            => ['nullable','integer'],
            'modules.*.client_id'     => ['nullable','integer'], // id temporal (negativo)
            'modules.*.name'          => ['required','string','max:255'],
            'modules.*.order'         => ['required','integer','min:1'],

            'chapters'                    => ['sometimes','array'],
            'chapters.*.id'               => ['nullable','integer'],
            'chapters.*.module_id'        => ['nullable','integer'],
            'chapters.*.client_module_id' => ['nullable','integer'], // id temporal del módulo destino
            'chapters.*.title'            => ['required','string','max:255'],
            'chapters.*.description'      => ['nullable','string'],
            'chapters.*.order'            => ['required','integer','min:1'],
        ]);

        $course = Course::findOrFail($data['course_id']);
        $this->authorize('update', $course);

        $removeMissingModules  = (bool)($data['remove_missing_modules']  ?? false);
        $removeMissingChapters = (bool)($data['remove_missing_chapters'] ?? false);

        return DB::transaction(function () use ($course, $data, $removeMissingModules, $removeMissingChapters) {

            // 1) Actualizar/crear módulos y normalizar orden.
            //    Devolvemos un mapa de client_id (negativos) -> id real en DB para referenciar desde capítulos
            $clientToRealModuleId = $this->applyModulesUpdate($course, $data['modules'], $removeMissingModules);

            // 2) Actualizar/crear/mover capítulos y normalizar orden por módulo
            $this->applyChaptersUpdate(
                $course,
                $data['chapters'] ?? [],
                $removeMissingChapters,
                $clientToRealModuleId
            );

            // 3) Respuesta: módulos con capítulos enriquecidos
            $modules = $course->modules()
                ->select('id','name','order','course_id','created_at','updated_at','deleted_at')
                ->with([
                    'chapters' => function ($q) {
                        $q->select('id','title','description','order','module_id','created_at','updated_at','deleted_at')
                          ->withCount('questions')
                          ->with(['learningContent' => function ($c) {
                              $c->select('id','type_content_id','chapter_id')
                                ->with(['typeLearningContent:id,name']);
                          }])
                          ->orderBy('order');
                    },
                ])
                ->orderBy('order')
                ->get();

            return response()->json([
                'ok'      => true,
                'modules' => $modules,
            ]);
        });
    }

    /**
     * Aplica cambios de módulos: crea/actualiza, elimina faltantes (opcional),
     * y normaliza el order 1..N. Devuelve un mapa client_id (negativo) => id real.
     *
     * @param \App\Models\Course $course
     * @param array $incomingModules
     * @param bool $removeMissing
     * @return array<int,int>  client_id => id
     */
    private function applyModulesUpdate(Course $course, array $incomingModules, bool $removeMissing): array
    {
        // Normalizamos entrada
        $incoming = collect($incomingModules)
            ->map(fn ($m) => [
                'id'        => $m['id'] ?? null,
                'client_id' => $m['client_id'] ?? null, // puede ser negativo
                'name'      => trim($m['name']),
                'order'     => (int) $m['order'],
                'course_id' => $course->id,
            ])
            ->sortBy('order')
            ->values();

        $existingIds = $incoming->pluck('id')->filter()->unique()->values();

        // Validar pertenencia
        if ($existingIds->isNotEmpty()) {
            $countBelong = Module::where('course_id', $course->id)
                ->whereIn('id', $existingIds)
                ->count();

            if ($countBelong !== $existingIds->count()) {
                abort(422, 'Hay módulos que no pertenecen a este curso.');
            }
        }

        // Foto antes de crear para removeMissing
        $existingIdsBeforeInsert = Module::where('course_id', $course->id)->pluck('id');

        // Eliminar faltantes (solo existentes previos)
        if ($removeMissing && $existingIdsBeforeInsert->isNotEmpty()) {
            $toDelete = $existingIdsBeforeInsert->diff($existingIds);
            if ($toDelete->isNotEmpty()) {
                Module::where('course_id', $course->id)
                    ->whereIn('id', $toDelete)
                    ->delete();
            }
        }

        // Actualizar existentes
        $incoming->whereNotNull('id')->each(function ($m) {
            Module::where('id', $m['id'])->update([
                'name'  => $m['name'],
                'order' => $m['order'],
            ]);
        });

        // Crear nuevos (guardando mapa client_id => id real)
        $clientToReal = [];
        $toCreate = $incoming->whereNull('id')->values();
        foreach ($toCreate as $m) {
            $created = Module::create([
                'name'      => $m['name'],
                'order'     => $m['order'],
                'course_id' => $m['course_id'],
            ]);
            if (!empty($m['client_id'])) {
                $clientToReal[(int)$m['client_id']] = $created->id;
            }
        }

        // Normalizar orden 1..N
        $finalModules = Module::where('course_id', $course->id)
            ->orderBy('order')
            ->get();

        $i = 1;
        foreach ($finalModules as $m) {
            if ((int)$m->order !== $i) {
                $m->order = $i;
                $m->save();
            }
            $i++;
        }

        return $clientToReal;
    }

    /**
     * Aplica cambios de capítulos: crea/actualiza/mueve, elimina faltantes (opcional),
     * y normaliza el order por módulo.
     *
     * Soporta referencias a módulos recién creados vía $clientToRealModuleId.
     *
     * @param \App\Models\Course $course
     * @param array $incomingChapters
     * @param bool $removeMissing
     * @param array<int,int> $clientToRealModuleId  client_id => real_id
     * @return void
     */
    private function applyChaptersUpdate(Course $course, array $incomingChapters, bool $removeMissing, array $clientToRealModuleId): void
    {
        // Si no hay capítulos en el payload
        if (empty($incomingChapters)) {
            if ($removeMissing) {
                // Si se solicita remover faltantes y NO se envían capítulos, vacía todos los capítulos del curso
                $moduleIds = Module::where('course_id', $course->id)->pluck('id');
                if ($moduleIds->isNotEmpty()) {
                    Chapter::whereIn('module_id', $moduleIds)->delete();
                }
            }
            return;
        }

        // Normaliza y resuelve module_id (prioriza client_module_id)
        $incoming = collect($incomingChapters)->map(function ($c) use ($clientToRealModuleId) {
            $resolvedModuleId = $c['module_id'] ?? null;

            if (!empty($c['client_module_id'])) {
                $clientKey = (int) $c['client_module_id'];
                if (!isset($clientToRealModuleId[$clientKey])) {
                    abort(422, "No se pudo resolver client_module_id={$clientKey} a un módulo creado.");
                }
                $resolvedModuleId = $clientToRealModuleId[$clientKey];
            }

            if (empty($resolvedModuleId)) {
                abort(422, 'Cada capítulo debe indicar module_id o client_module_id.');
            }

            return [
                'id'          => $c['id'] ?? null,
                'title'       => trim($c['title']),
                'description' => $c['description'] ?? null,
                'order'       => (int) ($c['order'] ?? 1),
                'module_id'   => (int) $resolvedModuleId,
            ];
        })->sortBy(['module_id','order'])->values();

        // Validar: IDs existentes pertenecen a este curso
        $chapterIds = $incoming->pluck('id')->filter()->unique()->values();
        if ($chapterIds->isNotEmpty()) {
            // Traemos los módulos de esos capítulos y verificamos que sean del curso
            $countBelong = Chapter::whereIn('id', $chapterIds)
                ->whereIn('module_id', function ($q) use ($course) {
                    $q->select('id')->from('modules')->where('course_id', $course->id);
                })
                ->count();

            if ($countBelong !== $chapterIds->count()) {
                abort(422, 'Hay capítulos que no pertenecen a este curso.');
            }
        }

        // Eliminar faltantes (capítulos del curso que no vienen)
        if ($removeMissing) {
            $moduleIds = Module::where('course_id', $course->id)->pluck('id');
            if ($moduleIds->isNotEmpty()) {
                $incomingExistingChapterIds = $chapterIds;
                $toDelete = Chapter::whereIn('module_id', $moduleIds)
                    ->pluck('id')
                    ->diff($incomingExistingChapterIds);

                if ($toDelete->isNotEmpty()) {
                    Chapter::whereIn('id', $toDelete)->delete();
                }
            }
        }

        // Upsert manual (update existentes / create nuevos)
        foreach ($incoming as $c) {
            if (!empty($c['id'])) {
                // update (soporta mover de módulo al cambiar module_id)
                Chapter::where('id', $c['id'])->update([
                    'title'       => $c['title'],
                    'description' => $c['description'],
                    'order'       => $c['order'],
                    'module_id'   => $c['module_id'],
                ]);
            } else {
                // create
                Chapter::create([
                    'title'       => $c['title'],
                    'description' => $c['description'],
                    'order'       => $c['order'],
                    'module_id'   => $c['module_id'],
                ]);
            }
        }

        // Normalizar orden 1..N por módulo
        $moduleIdsToNormalize = $incoming->pluck('module_id')->unique()->values();
        if ($moduleIdsToNormalize->isEmpty()) {
            // Abarcar todos los módulos del curso por si remove_missing movió cosas
            $moduleIdsToNormalize = Module::where('course_id', $course->id)->pluck('id');
        }

        foreach ($moduleIdsToNormalize as $mid) {
            $chapters = Chapter::where('module_id', $mid)->orderBy('order')->get();
            $i = 1;
            foreach ($chapters as $ch) {
                if ((int)$ch->order !== $i) {
                    $ch->order = $i;
                    $ch->save();
                }
                $i++;
            }
        }
    }


}
