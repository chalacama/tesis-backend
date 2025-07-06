<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    CourseController,StartController,RegistrationController,WatchingController,ModuleController,
    ChapterController,LearningContentController,TutorCourseController,AuthController
};
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')->group(function () {
    Route::post('/google/callback', [AuthController::class, 'handleGoogleCallback']);
    // Registro tradicional
    Route::post('/register', [AuthController::class, 'register']);
    // Login tradicional
    Route::post('/login', [AuthController::class, 'login']);
    // Logout (protegida por Sanctum)
    // Solo un usuario autenticado puede cerrar sesión.
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
});

Route::prefix('course')->group(function () {
    Route::post('/create', [CourseController::class, 'createCourse']);
    Route::put('/{id}/update', [CourseController::class, 'updateCourse']);
    Route::put('/{id}/activate', [CourseController::class, 'activateCourse']);
    Route::delete('/{id}/soft-delete', [CourseController::class, 'softDeleteCourse']);
    Route::get('/all', [CourseController::class, 'getAllCourses']);
    Route::get('/{id}/detail', [CourseController::class, 'getCourseDetail']);

});
Route::prefix('module')->group(function () {
    Route::post('/create', [ModuleController::class, 'createModule']);
    Route::put('/{id}/update', [ModuleController::class, 'updateModule']);
    Route::put('/{id}/activate', [ModuleController::class, 'activateModule']);
    Route::delete('/{id}/soft-delete', [ModuleController::class, 'softDeleteModule']);
    Route::post('/update-order', [ModuleController::class, 'updateOrderModules']);

});
Route::prefix('chapter')->group(function () {
    Route::post('/create', [ChapterController::class, 'createChapter']);
    Route::put('/{id}/update', [ChapterController::class, 'updateChapter']);
    Route::delete('/{id}/soft-delete', [ChapterController::class, 'softDeleteChapter']);
    Route::post('/update-order', [ChapterController::class, 'updateOrderChapters']);
    Route::put('/{id}/activate', [ChapterController::class, 'activateChapte']);
});
Route::prefix('learning-content')->group(function () {
    Route::prefix('/cloudinary')->group(function () {
        Route::post('/create-video', [LearningContentController::class, 'createVideoCloudinary']);
        Route::delete('/{id}/destroy-video', [LearningContentController::class, 'destroyVideoCloudinary']);
    });
    Route::delete('/{id}/soft-delete', [LearningContentController::class, 'softDeleteModule']);
    Route::put('/{id}/activate', [LearningContentController::class, 'activateLearningContent']);     
});
Route::prefix('tutor-course')->group(function () {
    Route::post('/create', [TutorCourseController::class, 'create'])
        ->middleware(['auth:sanctum', 'permission:asignar tutor a cursos'])
        ->name('tutor-course.create');
});





Route::prefix('start')->group(function () {
    Route::get('/top-popular-courses', [StartController::class, 'topPopularCourses']);
    Route::get('/top-best-rated-courses', [StartController::class, 'topBestRatedCourses']);
    Route::get('/top-updated-courses', [StartController::class, 'topUpdatedCourses']);
    Route::get('/top-created-courses', [StartController::class, 'topCreatedCourses']);
    Route::get('/recommend-courses/{userId}', [StartController::class, 'recommendCoursesByUserInterest']);
    
});
Route::prefix('register')->group(function () {
    Route::post('/user-to-course', [RegistrationController::class, 'registerUserToCourse']);
    Route::post('/cancel-user-to-course', [RegistrationController::class, 'cancelRegistrationUserToCourse']);

}); 
Route::prefix('watching')->group(function () {
    Route::get('/{courseId}/list-content/{userId}', [WatchingController::class, 'getListContent']);
    Route::post('/youtube-detail', [WatchingController::class, 'getYtVideoDetail']);
    Route::post('/content-view', [WatchingController::class, 'getContentViewById']);
}); 
