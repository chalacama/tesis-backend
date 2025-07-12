<?php

namespace App\Providers;

use App\Models\Course;
use App\Models\Module;
use App\Policies\ModulePolicy;
use App\Policies\CoursePolicy;
// use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // Asocia el modelo Course con su Policy
        Course::class => CoursePolicy::class,
        Module::class => ModulePolicy::class,
    ];
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
