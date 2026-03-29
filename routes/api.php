<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Job\EmployerJobController;
use App\Http\Controllers\Api\V1\Job\EmployerJobApplicationController;
use App\Http\Controllers\Api\V1\Job\FreelanceJobController;
use App\Http\Controllers\Api\V1\Job\FreelanceJobApplicationController;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->name('api.v1.auth.register');
        Route::post('login', [AuthController::class, 'login'])->name('api.v1.auth.login');
    });


    Route::middleware('auth:sanctum')->group(function () {

        Route::prefix('auth')->group(function () {
            Route::get('me', [AuthController::class, 'me'])->name('api.v1.auth.me');
            Route::post('logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
            Route::post('logout-all', [AuthController::class, 'logoutAll'])->name('api.v1.auth.logoutAll');
        });

        Route::prefix('employer')
            ->middleware('role:employer')
            ->group(function () {

                Route::apiResource('jobs', EmployerJobController::class)->names('api.v1.employer.jobs');
                Route::get('jobs/{job}/applications', [EmployerJobApplicationController::class, 'index'])->name('api.v1.employer.jobs.applications.index');
                Route::get('applications/{application}', [EmployerJobApplicationController::class, 'show'])->name('api.v1.employer.applications.show');
                Route::get('applications/{application}/cv', [EmployerJobApplicationController::class, 'downloadCv'])->name('api.v1.employer.applications.cv');

        });

        Route::prefix('freelancer')
            ->middleware('role:freelancer')
            ->group(function () {

                Route::get('jobs', [FreelanceJobController::class, 'index'])->name('api.v1.freelancer.jobs.index');
                Route::get('jobs/{job}', [FreelanceJobController::class, 'show'])->name('api.v1.freelancer.jobs.show');
                Route::post('jobs/{job}/apply', [FreelanceJobApplicationController::class, 'apply'])->name('api.v1.freelancer.jobs.apply');
                Route::get('applications', [FreelanceJobApplicationController::class, 'myApplications'])->name('api.v1.freelancer.applications.index');

            });

    });

});
