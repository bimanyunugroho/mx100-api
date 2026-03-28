<?php

namespace App\Providers;

use App\Repositories\Contracts\Auth\AuthRepositoryInterface;
use App\Repositories\Contracts\Job\JobApplicationRepositoryInterface;
use App\Repositories\Contracts\Job\JobRepositoryInterface;
use App\Repositories\Eloquents\Auth\AuthRepository;
use App\Repositories\Eloquents\Job\JobApplicationRepository;
use App\Repositories\Eloquents\Job\JobRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $repositories = [
            AuthRepositoryInterface::class => AuthRepository::class,
            JobRepositoryInterface::class => JobRepository::class,
            JobApplicationRepositoryInterface::class => JobApplicationRepository::class,
        ];

        foreach ($repositories as $interface => $repository) {
            $this->app->bind($interface, $repository);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
