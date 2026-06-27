<?php

namespace App\Providers;

use App\Contracts\ProjectRepository\ProjectRepositoryInterface;
use App\Contracts\ProjectRepository\ProjectServiceInterface;
use App\Contracts\TaskRepository\TaskRepositoryInterface;
use App\Contracts\TaskRepository\TaskServiceInterface;
use App\Models\Task;
use App\Observers\TaskObserver;
use App\Repositories\ProjectRepository;
use App\Repositories\TaskRepository;
use App\Services\ProjectService;
use App\Services\TaskService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            ProjectRepositoryInterface::class,
            ProjectRepository::class
        );

        $this->app->bind(
            ProjectServiceInterface::class,
            ProjectService::class
        );

        $this->app->bind(
            TaskRepositoryInterface::class,
            TaskRepository::class
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
