<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    CourseController,StartController,RegistrationController,WatchingController,ModuleController,
    ChapterController,LearningContentController,TutorCourseController,AuthController,
    CourseInvitationController,UserInformationController, EducationalUserController, SedeController,
    DifficultyController,PortfolioController,MiniatureCourseController, CategoryController, CareerController,
    QuestionController, TypeQuestionController,TypeLearningContentController, LikeChapterController,
    SavedCourseController, ContentViewController, CommentController, LikeCommentController,
    CompletedChapterController, TestController, HistoryController, CertificateController, EducationalLevelController,
    ImageProxyController, NotificationController, UserCategoryInterestController, UserController, 
    RatingCourseController, PanelController, RoleController, EcuadorLocationController, EducationalUnitController,
    TypeThumbnailController, VerificationCodeController
};



// == RUTAS PÚBLICAS Y DE AUTENTICACIÓN ==
Route::get('/user', function (Request $request) {
    return $request->user()?->load('roles'); // Carga los roles si el usuario existe
})->middleware('auth:sanctum');
Route::get('/prueba-google', function () {
    try {
        // Guardamos el resultado de la subida en una variable ($exito será true o false)
        $exito = Storage::disk('gcs')->put('hola-mundo.txt', '¡Hola! Este archivo viene desde mi Laravel local.');
        
        if (!$exito) {
            throw new \Exception('Laravel devolvió "false". No se pudo subir el archivo.');
        }
        
        return response()->json([
            'status' => '¡ÉXITO REAL!',
            'mensaje' => 'El archivo se subió correctamente a Google Cloud Storage.'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'ERROR',
            'mensaje' => $e->getMessage()
        ]);
    }
});
Route::prefix('auth')->group(function () {
    Route::post('/google-start', [AuthController::class, 'googleStart']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

// == RUTAS PÚBLICAS DE VERIFICACIÓN (Password Reset - usuario NO autenticado) ==
Route::prefix('verification')->group(function () {
    Route::prefix('password-reset')->group(function () {
        Route::post('/send', [VerificationCodeController::class, 'sendCode']);
        Route::post('/verify', [VerificationCodeController::class, 'verifyCode']);
        Route::post('/check-status', [VerificationCodeController::class, 'checkStatus']);
    });
});
Route::prefix('certificate')->group(function () {
        Route::get('/show', [CertificateController::class, 'show']);
        Route::get('/image-proxy', [ImageProxyController::class, 'show']);
});
// == RUTAS DE GESTIÓN (Protegidas por autenticación y permisos) ==
Route::middleware('auth:sanctum')->group(function () {

    // == RUTAS PROTEGIDAS DE VERIFICACIÓN (Email/Phone - usuario autenticado) ==
    Route::prefix('verification')->group(function () {
        Route::post('/send', [VerificationCodeController::class, 'sendCode']);
        Route::post('/verify', [VerificationCodeController::class, 'verifyCode']);
        Route::post('/check-status', [VerificationCodeController::class, 'checkStatus']);
    });

    Route::prefix('course')->group(function () {
        Route::post('/store', [CourseController::class, 'store'])->middleware('permission:course.create');
        Route::post('/{course}/update', [CourseController::class, 'update'])->middleware('permission:course.update');
        Route::delete('/{course}/archived', [CourseController::class, 'archived'])->middleware('permission:course.archived');
        Route::get('/{course}/show', [CourseController::class, 'show'])->middleware('permission:course.read.hidden');
        Route::get('/index', [CourseController::class, 'index'])->middleware('permission:course.read.hidden');
        Route::put('/{course}/active', [CourseController::class, 'active'])->middleware('permission:course.update');
        Route::get('/generate-code', [CourseController::class, 'generateCode'])->middleware('permission:course.update');
        Route::patch('/{courseId}/restore', [CourseController::class, 'restore'])->middleware('permission:course.archived'); // o crea course.restore si quieres
        Route::delete('/{courseId}/force-delete', [CourseController::class, 'forceDestroy'])->middleware('permission:course.archived'); // o crea course.forceDelete
    });

    Route::prefix('studio')->group(function () {
        Route::get('/@{username}', [CourseController::class, 'showOwner'])->middleware('permission:course.read.hidden');
        Route::get('/{course}/show/miniature', [MiniatureCourseController::class, 'show'])->middleware('permission:course.read.hidden');
    });
    Route::prefix('dashboard')->group(function () {
        Route::get('/index', [PanelController::class, 'index'])->middleware('permission:course.read.hidden');
        Route::get('/{course}/show', [PanelController::class, 'show'])->middleware('permission:course.read.hidden');
    });
    Route::prefix('user')->group(function () {
        Route::get('/index', [UserController::class, 'index'])->middleware('permission:user.read.hidden');
        Route::put('/{user}/change-role', [UserController::class, 'changeRole'])->middleware('permission:user.read.hidden');
    });
    Route::prefix('role')->group(function () {
        Route::get('/index', [RoleController::class, 'index'])->middleware('permission:user.read.hidden');
        
    });

    Route::prefix('module')->group(function () {
        Route::get('/{course}/index', [ModuleController::class, 'index'])->middleware('permission:course.read.hidden');
        Route::post('/update', [ModuleController::class, 'update'])->middleware('permission:course.update');
    });

    Route::prefix('chapter')->group(function () {
        Route::get('/{chapter}/show', [ChapterController::class, 'show'])->middleware('permission:course.read.hidden');
        Route::put('/{chapter}/update', [ChapterController::class, 'update'])->middleware('permission:course.update');
        Route::prefix('learning-content')->group(function () {
            Route::get('/{chapter}/show', [LearningContentController::class, 'show'])->middleware('permission:course.read.hidden');
            Route::post('/{chapter}/update', [LearningContentController::class, 'update'])->middleware('permission:course.update');
        });
        Route::prefix('question')->group(function () {
            Route::get('/{chapter}/index', [QuestionController::class, 'index'])->middleware('permission:course.read.hidden');
            Route::post('/{chapter}/update', [QuestionController::class, 'update'])->middleware('permission:course.update');
        });
    });


    Route::prefix('type')->group(function () {
        Route::get('/index/question', [TypeQuestionController::class, 'index'])->middleware('permission:course.read');
        Route::get('/index/learning-content', [TypeLearningContentController::class, 'index'])->middleware('permission:course.read');
        Route::get('/index/thumbnail', [TypeThumbnailController::class, 'index'])->middleware('permission:course.read');
    });

    Route::prefix('collaborator')->group(function () {

        Route::get('/{course}/show', [CourseInvitationController::class, 'show'])->middleware('permission:course.tutor.collaborator.invite');

        Route::get('/validate', [CourseInvitationController::class, 'validate'])->middleware('permission:course.tutor.collaborator.invite');

        Route::delete('{course}/delete/{user}', [CourseInvitationController::class, 'deleteCollaborator'])->middleware('permission:course.tutor.collaborator.archived');

        Route::delete('{course}/delete-owner/{user}', [CourseInvitationController::class, 'deleteOwner'])->middleware('permission:course.tutor.owner.change');

        Route::delete('/{course}/leave', [CourseInvitationController::class, 'leave'])->middleware('permission:course.tutor.collaborator.archived');

        Route::post('/{course}/store', [CourseInvitationController::class, 'store'])->middleware('permission:course.tutor.collaborator.invite'); 

        Route::put('/{course}/change', [CourseInvitationController::class, 'change'])->middleware('permission:course.tutor.collaborator.invite');

        Route::delete('/{course}/cancel/{invitation}', [CourseInvitationController::class, 'cancel'])->middleware('permission:course.tutor.collaborator.invite');
        
        
    });

    Route::prefix('watching')->group(function () {
        Route::get('/course/{course}/show', [WatchingController::class, 'showCourse'])->middleware('permission:course.read');
        Route::get('/content/{chapter}/show', [WatchingController::class, 'showContent']);

        Route::get('/detail/{course}/show', [WatchingController::class, 'showDetail'])->middleware('permission:course.read');
        Route::get('/comment/{course}/index', [CommentController::class, 'index'])->middleware('permission:course.read');
        Route::post('/comment/{course}/store', [CommentController::class, 'store'])->middleware('permission:course.read');
        Route::get('/course/{course}/comment/{comment}/replies', [CommentController::class, 'replies'])->middleware('permission:course.read');
        Route::get('/test/{chapter}/index', [TestController::class, 'index'])->middleware('permission:course.read');
        Route::get('/test/{chapter}/show', [TestController::class, 'show'])->middleware('permission:course.read');
        Route::post('/test/{testView}/update', [TestController::class, 'update'])->middleware('permission:course.read');
        
    });

    Route::prefix('feedback')->group(function () {
        Route::post('/like/{chapter}/update', [LikeChapterController::class, 'update'])->middleware('permission:course.read');
        Route::post('/saved/{course}/update', [SavedCourseController::class, 'update'])->middleware('permission:course.read');
        Route::post('/rating/{course}/update', [RatingCourseController::class, 'update'])->middleware('permission:course.read');
        Route::post('/content/{learningContent}/update', [ContentViewController::class, 'update'])->middleware('permission:course.read');
        Route::post('/progress/{learningContent}/update', [CompletedChapterController::class, 'updateProgress'])->middleware('permission:course.read');
        Route::post('/completed/{testView}/test', [CompletedChapterController::class, 'completedTest'])->middleware('permission:course.read');

        Route::post('/comment/{comment}/update', [LikeCommentController::class, 'update'])->middleware('permission:course.read');


        Route::post('/register/{course}/store', [RegistrationController::class, 'store'])->middleware('permission:course.registration.create');
        Route::post('/code/store', [RegistrationController::class, 'code'])->middleware('permission:course.registration.create');
    });
    Route::prefix('start')->group(function () {
        Route::get('/courses-by-filter', [StartController::class, 'getCoursesByFilter'])->middleware('permission:course.read');
        Route::get('/courses-search', [StartController::class, 'searchCourses'])->middleware('permission:course.read');        
        Route::get('/portfolio-by-filter', [StartController::class, 'getPortfolioByFilter'])->middleware('permission:course.read');
        Route::get('/suggestion-by-filter', [StartController::class, 'getSuggestionByFilter'])->middleware('permission:course.read');
        Route::post('/suggestion', [StartController::class, 'updateSuggestion']); // <- para guardar historial
    });
    Route::prefix('profile')->group(function () {
        Route::prefix('/info')->group(function () {
            Route::get('/show', [UserInformationController::class, 'show'])->middleware('permission:profile.read');
            Route::put('/update', [UserInformationController::class, 'update'])->middleware('permission:profile.update');
        });
        Route::prefix('/education')->group(function () {
            Route::get('/show', [EducationalUserController::class, 'show'])->middleware('permission:profile.read');
            Route::put('/update', [EducationalUserController::class, 'update'])->middleware('permission:profile.update');
        });
        Route::prefix('/interest')->group(function () {
            Route::get('/show', [UserCategoryInterestController::class, 'show'])->middleware('permission:profile.read');
            Route::put('/update', [UserCategoryInterestController::class, 'update'])->middleware('permission:profile.update');
        });
        Route::prefix('/user')->group(function () {
            Route::put('/update', [UserController::class, 'update'])->middleware('permission:profile.update');
            Route::get('/validate-username', [UserController::class, 'validateUsername'])->middleware('permission:profile.update');
        });

        Route::prefix('portfolio')->group(function () {
            Route::get('/@{username}', [PortfolioController::class, 'show'])->middleware('permission:user.read');
        });
        Route::prefix('history')->group(function () {
            Route::get('/index', [HistoryController::class, 'index'])->middleware('permission:user.read');
        });
    });

    Route::prefix('certificate')->group(function () {
        Route::get('/index', [CertificateController::class, 'index'])->middleware('permission:course.read');
    });

    Route::prefix('sede')->group(function () {
        Route::get('/index', [SedeController::class, 'index'])->middleware('permission:education.read');
        Route::get('/index-admin', [SedeController::class, 'indexAdmin'])->middleware('permission:education.read.hidden');
        Route::post('/store', [SedeController::class, 'store'])->middleware('permission:education.create');
        Route::put('/{sede}/update', [SedeController::class, 'update'])->middleware('permission:education.update');
        Route::delete('{sede}/destroy', [SedeController::class, 'destroy'])->middleware('permission:education.update');
    });

    Route::prefix('edu-level')->group(function () {
        Route::get('/index', [EducationalLevelController::class, 'index'])->middleware('permission:education.read.hidden');
        Route::get('/index-admin', [EducationalLevelController::class, 'indexAdmin'])->middleware('permission:education.read.hidden');
        Route::post('/store', [EducationalLevelController::class, 'store'])->middleware('permission:education.create');
        Route::put('/{educationalLevel}/update', [EducationalLevelController::class, 'update'])->middleware('permission:education.update');
        Route::delete('{educationalLevel}/destroy', [EducationalLevelController::class, 'destroy'])->middleware('permission:education.update');
    });
    Route::prefix('edu-unit')->group(function () {
        Route::get('/index', [EducationalUnitController::class, 'index'])->middleware('permission:education.read.hidden');
        Route::get('/index-admin', [EducationalUnitController::class, 'indexAdmin'])->middleware('permission:education.read.hidden');
        Route::post('/store', [EducationalUnitController::class, 'store'])->middleware('permission:education.create');
        Route::put('/{educationalUnit}/update', [EducationalUnitController::class, 'update'])->middleware('permission:education.update');
        Route::delete('/{educationalUnit}/destroy', [EducationalUnitController::class, 'destroy'])->middleware('permission:education.update');
    });
    Route::prefix('career')->group(function () {
        Route::get('/index', [CareerController::class, 'index'])->middleware('permission:course.read');
        Route::get('/index-admin', [CareerController::class, 'indexAdmin'])->middleware('permission:course.setting.read.hidden');
        Route::post('/store', [CareerController::class, 'store'])->middleware('permission:course.setting.create');
        Route::put('/{career}/update', [CareerController::class, 'update'])->middleware('permission:course.setting.update');
        Route::delete('/{career}/destroy', [CareerController::class, 'destroy'])->middleware('permission:course.setting.update');
    });
    
    Route::prefix('difficulty')->group(function () {
        Route::get('/index', [DifficultyController::class, 'index'])->middleware('permission:course.read');
    });
    
    Route::prefix('category')->group(function () {
        Route::get('/index', [CategoryController::class, 'index'])->middleware('permission:course.read');
        Route::get('/index-admin', [CategoryController::class, 'indexAdmin'])->middleware('permission:course.setting.read.hidden');
        Route::post('/store', [CategoryController::class, 'store'])->middleware('permission:course.setting.create');
        Route::put('/{category}/update', [CategoryController::class, 'update'])->middleware('permission:course.setting.update');
        Route::delete('/{category}/destroy', [CategoryController::class, 'destroy'])->middleware('permission:course.setting.update');
    });

    Route::prefix('notifications')->group(function () {
        Route::get('/',            [NotificationController::class, 'index']);        // listar
        Route::get('/unread-count',[NotificationController::class, 'unreadCount']); // solo contador
        Route::post('/{id}/read',  [NotificationController::class, 'markAsRead']);   // marcar una
        Route::post('/read-all',   [NotificationController::class, 'markAllAsRead']); // marcar todas
    });

    Route::prefix('locations')->group(function () {
        // Todas las provincias
        Route::get('/provinces', [EcuadorLocationController::class, 'provinces'])->middleware('permission:profile.read');

        // Cantones por provincia
        Route::get('/provinces/{province}/cantons', [EcuadorLocationController::class, 'cantons'])->middleware('permission:profile.read');

        // Parroquias por provincia y cantón
        Route::get(
        '/provinces/{province}/cantons/{canton}/parishes',[EcuadorLocationController::class, 'parishes'])->middleware('permission:profile.read');
    });

});




//  Rutas públicas que no requieren token

Route::post('invitation/accept', [CourseInvitationController::class, 'accept']);


// ->middleware(['auth:sanctum', 'permission:asignar tutor a cursos'])->name('tutor-course.activate');
