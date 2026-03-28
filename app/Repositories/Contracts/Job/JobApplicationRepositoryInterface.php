<?php

namespace App\Repositories\Contracts\Job;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface JobApplicationRepositoryInterface
{
    public function create(array $data): JobApplication;

    public function hasApplied(User $freelancer, Job $job): bool;

    public function getByJob(Job $job, int $perPage = 15): LengthAwarePaginator;

    public function getByFreelancer(User $freelancer, int $perPage = 15): LengthAwarePaginator;

    public function findForEmployer(string $jobApplicationId, User $employer): ?JobApplication;
}
