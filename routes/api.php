<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    CourseController,StartController,RegistrationController,WatchingController,ModuleController,
    ChapterController,LearningContentController,TutorCourseController,AuthController,
    CourseInvitationController
};
// == RUTAS PÚBLICAS Y DE AUTENTICACIÓN ==
Route::get('/user', function (Request $request) {
    return $request->user()?->load('roles'); // Carga los roles si el usuario existe
})->middleware('auth:sanctum');

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/google/callback', [AuthController::class, 'handleGoogleCallback']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});
// == RUTAS DE GESTIÓN (Protegidas por autenticación y permisos) ==
Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('course')->group(function () {        
        Route::post('/store', [CourseController::class, 'store'])->middleware('permission:courses.create');
        Route::put('/{course}/update', [CourseController::class, 'update'])->middleware('permission:courses.update');
        Route::delete('/{course}/archived', [CourseController::class, 'archived'])->middleware('permission:courses.archived');
        Route::get('/{course}/show', [CourseController::class, 'show'])->middleware('permission:courses.read-hidden');
        Route::get('/index', [CourseController::class, 'index'])->middleware('permission:courses.read-hidden');
        Route::put('/{course}/active', [CourseController::class, 'active'])->middleware('permission:courses.update');
        Route::post('/{course}/reset-code', [CourseController::class, 'resetCode'])->middleware('permission:courses.update');
    });

    Route::prefix('module')->group(function () {
        Route::post('/store', [ModuleController::class, 'store'])->middleware('permission:modules.create');
        Route::put('/{module}/update', [ModuleController::class, 'update'])->middleware('permission:modules.update');
        Route::delete('/{module}/archived', [ModuleController::class, 'archived'])->middleware('permission:modules.archived');
        Route::post('/reorder', [ModuleController::class, 'reorder'])->middleware('permission:modules.update');
    });

    Route::prefix('chapter')->group(function () {
        Route::post('/store', [ChapterController::class, 'store'])->middleware('permission:chapters.create');
        Route::put('/{chapter}/update', [ChapterController::class, 'update'])->middleware('permission:chapters.update');
        Route::delete('/{chapter}/archived', [ChapterController::class, 'archived'])->middleware('permission:chapters.archived');
        Route::post('/reorder', [ChapterController::class, 'reorder'])->middleware('permission:chapters.update');
    });

    Route::prefix('learning-content')->group(function () {
        Route::prefix('/cloud')->group(function () {
            Route::post('/store', [LearningContentController::class, 'storeCloud'])->middleware('permission:learning-contents.create');            
            Route::delete('/{id}/destroy', [LearningContentController::class, 'destroyCloud'])->middleware('permission:learning-contents.destroy');
        });
        Route::delete('/{id}/archived', [LearningContentController::class, 'archived'])->middleware('permission:learning-contents.archived');
        Route::prefix('/youtube')->group(function () {            
        });
    });    
    Route::prefix('tutor-course')->group(function () {        
        // Route::post('/store', [TutorCourseController::class, 'store'])->middleware('permission:tutor-courses.create');
        // Route::post('/change', [TutorCourseController::class, 'change'])->middleware('permission:tutor-courses.update');
        // Route::delete('/{TutorCourse}/archived', [TutorCourseController::class, 'archived'])->middleware('permission:tutor-courses.archived');
    });
    Route::prefix('invitation')->group(function () {
        Route::post('{course}/store', [CourseInvitationController::class, 'store'])->middleware('permission:tutor-courses.invite-collaborator');    
    });
    Route::prefix('registration')->group(function () {
        Route::post('/store', [RegistrationController::class, 'store'])->middleware('permission:registration.create');
        Route::post('/cancel', [RegistrationController::class, 'cancel'])->middleware('permission:registration.cancel');
    });
    Route::prefix('watching')->group(function () {        
        Route::get('/{courseId}/course-index/{userId}', [WatchingController::class, 'indexCourse'])->middleware('permission:courses.read');        
        Route::post('/content-show', [WatchingController::class, 'showContent'])->middleware('permission:courses.read');
        Route::post('/yt-show', [WatchingController::class, 'showYt'])->middleware('permission:courses.read-hidden');        
});  
});




//  Rutas públicas que no requieren token
Route::prefix('start')->group(function () {
    Route::get('/top-popular-courses', [StartController::class, 'topPopularCourses']);
    Route::get('/top-best-rated-courses', [StartController::class, 'topBestRatedCourses']);
    Route::get('/top-updated-courses', [StartController::class, 'topUpdatedCourses']);
    Route::get('/top-created-courses', [StartController::class, 'topCreatedCourses']);
    // esta ruta aun no esta lista
    // Route::get('/recommend-courses/{userId}', [StartController::class, 'recommendCoursesByUserInterest'])->middleware(['auth:sanctum', 'permission:courses.read']);
    
});
Route::post('invitation/accept', [CourseInvitationController::class, 'accept']);


// ->middleware(['auth:sanctum', 'permission:asignar tutor a cursos'])->name('tutor-course.activate');