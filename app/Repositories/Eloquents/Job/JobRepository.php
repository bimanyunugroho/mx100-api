<?php

namespace App\Repositories\Eloquents\Job;

use App\DTOs\Job\CreateJobDataDTO;
use App\DTOs\Job\JobFilterDataDTO;
use App\DTOs\Job\UpdateJobDataDTO;
use App\Enums\StatusJobEnum;
use App\Models\Job;
use App\Models\User;
use App\Repositories\Contracts\Job\JobRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class JobRepository implements JobRepositoryInterface
{

    public function create(User $employer, CreateJobDataDTO $createJobDataDTO): Job
    {
        return $employer->jobPostings()->create([
            'title'        => $createJobDataDTO->title,
            'description'  => $createJobDataDTO->description,
            'requirements' => $createJobDataDTO->requirements,
            'salary_range' => $createJobDataDTO->salary_range,
            'location'     => $createJobDataDTO->location,
            'type'         => $createJobDataDTO->type,
            'status'       => $createJobDataDTO->status
        ]);
    }

    public function update(Job $job, UpdateJobDataDTO $updateJobDataDTO): Job
    {
        $payload = array_filter([
            'title'        => $updateJobDataDTO->title,
            'description'  => $updateJobDataDTO->description,
            'requirements' => $updateJobDataDTO->requirements,
            'salary_range' => $updateJobDataDTO->salary_range,
            'location'     => $updateJobDataDTO->location,
            'type'         => $updateJobDataDTO->type?->value,
            'status'       => $updateJobDataDTO->status?->value,
        ], fn ($value) => ! is_null($value));

        $job->update($payload);

        return $job->fresh();
    }

    public function delete(Job $job): bool
    {
        return (bool) $job->delete();
    }

    public function getByEmployer(User $employer, int $perPage = 10): LengthAwarePaginator
    {
        return $employer->jobPostings()
            ->withCount('applications')
            ->latest()
            ->paginate($perPage);
    }

    public function getPublished(JobFilterDataDTO $jobFilterDataDTO): LengthAwarePaginator
    {
        $query = QueryBuilder::for(Job::class)
            ->published()
            ->with('employer')
            ->withCount('applications')
            ->allowedFilters(
                AllowedFilter::scope('location'),
                AllowedFilter::exact('type'),
                AllowedFilter::scope('search')
            )
            ->allowedSorts(
                'published_at',
                'created_at',
                'title'
            )
            ->defaultSort('-published_at');

        if (!empty($jobFilterDataDTO->search)) {
            $query->search($jobFilterDataDTO->search);
        }

        if (!empty($jobFilterDataDTO->location)) {
            $query->location($jobFilterDataDTO->location);
        }

        if ($jobFilterDataDTO->type?->value) {
            $query->ofType($jobFilterDataDTO->type->value);
        }

        return $query->paginate($jobFilterDataDTO->per_page);
    }

    public function findPublished(string $id): ?Job
    {
        return Job::published()
            ->with('employer')
            ->find($id);
    }

    public function findByEmployer(string $id, User $employer): ?Job
    {
        return $employer->jobPostings()->find($id);
    }
}
