<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    CourseController,StartController,RegistrationController,WatchingController,ModuleController,
    ChapterController,LearningContentController,TutorCourseController,AuthController
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
        // Route::post('/create', [CourseController::class, 'createCourse'])->middleware('permission:courses.create');
        // Route::put('/{id}/update', [CourseController::class, 'updateCourse'])->middleware('permission:courses.update');
        // Route::put('/{id}/activate', [CourseController::class, 'activateCourse'])->middleware('permission:courses.activate');
        // Route::delete('/{id}/soft-delete', [CourseController::class, 'softDeleteCourse'])->middleware('permission:courses.delete');               
        // Route::get('/all', [CourseController::class, 'getAllCourses'])->middleware('permission:courses.read-hidden');        
        // Route::get('/{id}/detail', [CourseController::class, 'getCourseDetail'])->middleware('permission:read-hidden');
    Route::post('/create', [CourseController::class, 'createCourse'])->middleware('permission:courses.create');
    Route::put('/{course}/update', [CourseController::class, 'updateCourse'])->middleware('permission:courses.update');
    Route::put('/{course}/activate', [CourseController::class, 'activateCourse'])->middleware('permission:courses.activate');
    Route::delete('/{course}/soft-delete', [CourseController::class, 'softDeleteCourse'])->middleware('permission:courses.delete');
    Route::get('/detail', [CourseController::class, 'getCourseDetail'])->middleware('permission:read-hidden');
    Route::get('/{course}/all', [CourseController::class, 'getAllCourses'])->middleware('permission:courses.read-hidden');
    });

    Route::prefix('module')->group(function () {
        Route::post('/create', [ModuleController::class, 'createModule'])->middleware('permission:modules.create');
        Route::put('/{id}/update', [ModuleController::class, 'updateModule'])->middleware('permission:modules.update');
        Route::put('/{id}/activate', [ModuleController::class, 'activateModule'])->middleware('permission:modules.activate');
        Route::delete('/{id}/soft-delete', [ModuleController::class, 'softDeleteModule'])->middleware('permission:modules.delete');
        Route::post('/update-order', [ModuleController::class, 'updateOrderModules'])->middleware('permission:modules.update-order');
    });

    Route::prefix('chapter')->group(function () {
        Route::post('/create', [ChapterController::class, 'createChapter'])->middleware('permission:chapters.create');
        Route::put('/{id}/update', [ChapterController::class, 'updateChapter'])->middleware('permission:chapters.update');
        Route::put('/{id}/activate', [ChapterController::class, 'activateChapte'])->middleware('permission:chapters.activate');
        Route::delete('/{id}/soft-delete', [ChapterController::class, 'softDeleteChapter'])->middleware('permission:chapters.delete');
        Route::post('/update-order', [ChapterController::class, 'updateOrderChapters'])->middleware('permission:chapters.update-order');
    });

    Route::prefix('learning-content')->group(function () {
        Route::prefix('/cloudinary')->group(function () {
            Route::post('/create-video', [LearningContentController::class, 'createVideoCloudinary'])->middleware('permission:learning-contents.create');
            
            Route::delete('/{id}/destroy-video', [LearningContentController::class, 'destroyVideoCloudinary'])->middleware('permission:learning-contents.destroy');
        });
        Route::delete('/{id}/soft-delete', [LearningContentController::class, 'softDeleteModule'])->middleware('permission:learning-contents.delete');
        Route::put('/{id}/activate', [LearningContentController::class, 'activateLearningContent'])->middleware('permission:learning-contents.activate');
    });

    
    Route::prefix('tutor-course')->group(function () {
        Route::post('/create', [TutorCourseController::class, 'createTutorCourse'])->middleware('permission:tutor-courses.create');
        Route::post('/change', [TutorCourseController::class, 'changeTutorCourse'])->middleware('permission:tutor-courses.update');
        Route::put('/{id}/activate', [TutorCourseController::class, 'activateTutorCourse'])->middleware('permission:tutor-courses.activate');
        Route::delete('/{id}/destroy', [TutorCourseController::class, 'destroyTutorCourse'])->middleware('permission:tutor-courses.destroy');
    });
    Route::prefix('register')->group(function () {
        Route::post('/user-to-course', [RegistrationController::class, 'registerUserToCourse'])->middleware('permission:tutor-courses.delete');
        Route::post('/cancel-user-to-course', [RegistrationController::class, 'cancelRegistrationUserToCourse'])->middleware('permission:tutor-courses.delete');

    });
    Route::prefix('watching')->group(function () {
        Route::get('/{courseId}/list-content/{userId}', [WatchingController::class, 'getListContent'])->middleware('permission:courses.read');
        Route::post('/youtube-detail', [WatchingController::class, 'getYtVideoDetail'])->middleware('permission:courses.read-hidden');
        Route::post('/content-view', [WatchingController::class, 'getContentViewById'])->middleware('permission:courses.read');
}); 
});




//  Rutas públicas que no requieren token
Route::prefix('start')->group(function () {
    Route::get('/top-popular-courses', [StartController::class, 'topPopularCourses']);
    Route::get('/top-best-rated-courses', [StartController::class, 'topBestRatedCourses']);
    Route::get('/top-updated-courses', [StartController::class, 'topUpdatedCourses']);
    Route::get('/top-created-courses', [StartController::class, 'topCreatedCourses']);
    Route::get('/recommend-courses/{userId}', [StartController::class, 'recommendCoursesByUserInterest'])->middleware(['auth:sanctum', 'permission:courses.read']);
    
});



// ->middleware(['auth:sanctum', 'permission:asignar tutor a cursos'])->name('tutor-course.activate');