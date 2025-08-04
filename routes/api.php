<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    CourseController,StartController,RegistrationController,WatchingController,ModuleController,
    ChapterController,LearningContentController,TutorCourseController,AuthController,
    CourseInvitationController,UserInformationController, EducationalUserController, SedeController,
    DifficultyController,PortfolioController
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
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware('signed')
    ->name('verification.verify');
});
// == RUTAS DE GESTIÓN (Protegidas por autenticación y permisos) ==
Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('course')->group(function () {        
        Route::post('/store', [CourseController::class, 'store'])->middleware('permission:course.create');
        Route::put('/{course}/update', [CourseController::class, 'update'])->middleware('permission:course.update');
        Route::delete('/{course}/archived', [CourseController::class, 'archived'])->middleware('permission:course.archived');
        Route::get('/{course}/show', [CourseController::class, 'show'])->middleware('permission:course.read.hidden');
        Route::get('/index', [CourseController::class, 'index'])->middleware('permission:course.read.hidden');
        Route::put('/{course}/active', [CourseController::class, 'active'])->middleware('permission:course.update');
        Route::post('/{course}/reset-code', [CourseController::class, 'resetCode'])->middleware('permission:course.update');
        Route::get('/@{username}', [CourseController::class, 'showOwner'])->middleware('permission:course.read.hidden');
    });

    Route::prefix('module')->group(function () {
        Route::post('/store', [ModuleController::class, 'store'])->middleware('permission:course.create');
        Route::put('/{module}/update', [ModuleController::class, 'update'])->middleware('permission:course.update');
        Route::delete('/{module}/archived', [ModuleController::class, 'archived'])->middleware('permission:course.archived');
        Route::post('/reorder', [ModuleController::class, 'reorder'])->middleware('permission:course.update');
    });

    Route::prefix('chapter')->group(function () {
        Route::post('/store', [ChapterController::class, 'store'])->middleware('permission:course.create');
        Route::put('/{chapter}/update', [ChapterController::class, 'update'])->middleware('permission:course.update');
        Route::delete('/{chapter}/archived', [ChapterController::class, 'archived'])->middleware('permission:course.archived');
        Route::post('/reorder', [ChapterController::class, 'reorder'])->middleware('permission:course.update');
    });

    Route::prefix('learning-content')->group(function () {
        Route::prefix('/cloud')->group(function () {
            Route::post('/store', [LearningContentController::class, 'storeCloud'])->middleware('permission:course.create');            
            Route::delete('/{id}/destroy', [LearningContentController::class, 'destroyCloud'])->middleware('permission:course.destroy');
        });
        Route::delete('/{id}/archived', [LearningContentController::class, 'archived'])->middleware('permission:course.archived');
        Route::prefix('/youtube')->group(function () {            
        });
    });    
    Route::prefix('tutor-course')->group(function () {        
        // Route::post('/store', [TutorCourseController::class, 'store'])->middleware('permission:tutor-courses.create');
        // Route::post('/change', [TutorCourseController::class, 'change'])->middleware('permission:tutor-courses.update');
        // Route::delete('/{TutorCourse}/archived', [TutorCourseController::class, 'archived'])->middleware('permission:tutor-courses.archived');
    });
    Route::prefix('invitation')->group(function () {
        Route::post('{course}/store', [CourseInvitationController::class, 'store'])->middleware('permission:tutor-course.collaborator.invite');    
    });
    Route::prefix('registration')->group(function () {
        Route::post('/store', [RegistrationController::class, 'store'])->middleware('permission:course.registration.create');
        Route::post('/cancel', [RegistrationController::class, 'cancel'])->middleware('permission:course.registration.cancel');
    });
    Route::prefix('watching')->group(function () {        
        Route::get('/{courseId}/course-index/{userId}', [WatchingController::class, 'indexCourse'])->middleware('permission:course.read');        
        Route::post('/content-show', [WatchingController::class, 'showContent'])->middleware('permission:course.read');
        Route::post('/yt-show', [WatchingController::class, 'showYt'])->middleware('permission:course.read.hidden');        
    });  
    Route::prefix('start')->group(function () {
    Route::get('/courses-by-filter', [StartController::class, 'getCoursesByFilter'])->middleware('permission:course.read');

    });
    Route::prefix('profile')->group(function () {
        Route::prefix('/info')->group(function () {    
            Route::get('/show', [UserInformationController::class, 'show'])->middleware('permission:profile.read.hidden');
            Route::put('/update', [UserInformationController::class, 'update'])->middleware('permission:profile.update');   
        });
        Route::prefix('/education')->group(function () {    
            Route::get('/show', [EducationalUserController::class, 'show'])->middleware('permission:profile.read.hidden');
            Route::put('/update', [EducationalUserController::class, 'update'])->middleware('permission:profile.update');
        });     
    });
    Route::prefix('sede')->group(function () {    
            Route::get('/show', [SedeController::class, 'show'])->middleware('permission:education.read');
            Route::put('/update', [SedeController::class, 'update'])->middleware('permission:education.update');
            Route::get('/index', [SedeController::class, 'index'])->middleware('permission:education.read'); 
    });
    Route::prefix('difficulty')->group(function () {    
        Route::get('/index', [DifficultyController::class, 'index'])->middleware('permission:course.setting.read'); 
    });
    Route::prefix('portfolio')->group(function () {    
        Route::get('/@{username}', [PortfolioController::class, 'show'])->middleware('permission:user.read'); 
    });

});




//  Rutas públicas que no requieren token

Route::post('invitation/accept', [CourseInvitationController::class, 'accept']);


// ->middleware(['auth:sanctum', 'permission:asignar tutor a cursos'])->name('tutor-course.activate');