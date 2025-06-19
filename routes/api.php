<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    CourseController,


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