<?php

namespace App\Repositories\Eloquents\Job;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use App\Repositories\Contracts\Job\JobApplicationRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;

class JobApplicationRepository implements JobApplicationRepositoryInterface
{

    public function create(array $data): JobApplication
    {
        return JobApplication::create($data);
    }

    public function hasApplied(User $freelancer, Job $job): bool
    {
        return JobApplication::query()
            ->where('freelancer_id', $freelancer->id)
            ->where('job_id', $job->id)
            ->exists();
    }

    public function getByJob(Job $job, int $perPage = 15): LengthAwarePaginator
    {
        return $job->applications()
            ->with('freelancer:id,name,email,phone')
            ->latest()
            ->paginate($perPage);
    }

    public function getByFreelancer(User $freelancer, int $perPage = 15): LengthAwarePaginator
    {
        return $freelancer->applications()
            ->with([
                'job:id,title,status,employer_id,location,type',
                'job.employer:id,name,company_name',
            ])
            ->latest()
            ->paginate($perPage);
    }

    public function findForEmployer(string $jobApplicationId, User $employer): ?JobApplication
    {
        return JobApplication::whereHas('job', function ($query) use ($employer) {
            $query->where('employer_id', $employer->id);
        })
            ->with([
                'freelancer:id,name,email,phone',
                'job:id,title,employer_id',
            ])
            ->find($applicationId);
    }
}
