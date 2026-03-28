<?php

namespace App\Repositories\Contracts\Job;

use App\DTOs\Job\CreateJobDataDTO;
use App\DTOs\Job\JobFilterDataDTO;
use App\DTOs\Job\UpdateJobDataDTO;
use App\Models\Job;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface JobRepositoryInterface
{
    public function create(User $employer, CreateJobDataDTO $createJobDataDTO): Job;

    public function update(Job $job, UpdateJobDataDTO $updateJobDataDTO): Job;

    public function delete(Job $job): bool;

    public function getByEmployer(User $employer, int $perPage = 15): LengthAwarePaginator;

    public function getPublished(JobFilterDataDTO $jobFilterDataDTO): LengthAwarePaginator;

    public function findPublished(string $id): ?Job;

    public function findByEmployer(string $id, User $employer): ?Job;
}
