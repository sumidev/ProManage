<?php

namespace App\Providers;

use App\Models\Task;
use App\Observers\TaskObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Contracts\ProjectRepositoryInterface::class,
            \App\Repositories\ProjectRepository::class
        );

        $this->app->bind(
            \App\Contracts\ProjectServiceInterface::class,
            \App\Services\ProjectService::class
        );

        $this->app->bind(
            \App\Contracts\TaskRepositoryInterface::class,
            \App\Repositories\TaskRepository::class
        );

        $this->app->bind(
            \App\Contracts\TaskServiceInterface::class,
            \App\Services\TaskService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Task::observe(TaskObserver::class);

        // Invitation listeners are auto-discovered from app/Listeners (do not register manually — causes duplicates).
    }
}
