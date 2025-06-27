<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    CourseController,StartController,RegistrationController

};
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('course')->group(function () {
    Route::post('/create', [CourseController::class, 'createCourse']);
    Route::put('/{id}/update', [CourseController::class, 'updateCourse']);
    Route::put('/{id}/publish', [CourseController::class, 'publishCourse']);
    Route::delete('/{id}/delete', [CourseController::class, 'deleteCourse']);
    Route::get('/all', [CourseController::class, 'getAllCourses']);
    Route::get('/{id}/detail', [CourseController::class, 'getCourseDetail']);

});
Route::prefix('start')->group(function () {
    Route::get('/top-popular-courses', [StartController::class, 'topPopularCourses']);
    Route::get('/top-best-rated-courses', [StartController::class, 'topBestRatedCourses']);
    Route::get('/top-publish-courses', [StartController::class, 'topPublishCourses']);
    Route::get('/top-created-courses', [StartController::class, 'topCreatedCourses']);
    Route::get('/recommend-courses/{userId}', [StartController::class, 'recommendCoursesByUserInterest']);
    
});
Route::prefix('register')->group(function () {
    Route::get('/{courseId}/course-detail/{userId}', [RegistrationController::class, 'getCourseDetail']);
    Route::post('/youtube-detail', [RegistrationController::class, 'getYtVideoDetail']);
    Route::post('/user-to-course', [RegistrationController::class, 'registerUserToCourse']);

}); 