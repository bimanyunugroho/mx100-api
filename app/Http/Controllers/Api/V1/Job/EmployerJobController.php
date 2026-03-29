<?php

namespace App\Http\Controllers\Api\V1\Job;

use App\DTOs\Job\CreateJobDataDTO;
use App\DTOs\Job\UpdateJobDataDTO;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Job\StoreJobRequest;
use App\Http\Requests\Api\V1\Job\UpdateJobRequest;
use App\Http\Resources\Api\V1\Job\JobCollection;
use App\Http\Resources\Api\V1\Job\JobResource;
use App\Services\Job\JobService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployerJobController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly JobService $jobService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $jobs = $this->jobService->getEmployerJobs(
                employer: $request->user(),
                perPage:  (int) $request->query('per_page', 10),
            );

            return $this->paginated(
                new JobCollection($jobs),
                'Daftar lowongan pekerjaan berhasil diambil.',
            );
        } catch (ApiException $e) {
            return $this->error($e->getMessage(), $e->getStatusCode(), $e->getErrors());
        }
    }

    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $job = $this->jobService->getEmployerJob($request->user(), $id);

            return $this->ok(
                ['job' => new JobResource($job)],
            );
        } catch (ApiException $e) {
            return $this->error($e->getMessage(), $e->getStatusCode(), $e->getErrors());
        }
    }

    public function store(StoreJobRequest $request): JsonResponse
    {
        try {
            $job = $this->jobService->create(
                employer: $request->user(),
                createJobDataDTO:     CreateJobDataDTO::from($request->validated()),
            );

            return $this->created(
                ['job' => new JobResource($job)],
                'Job berhasil dibuat.',
            );
        } catch (ApiException $e) {
            return $this->error($e->getMessage(), $e->getStatusCode(), $e->getErrors());
        }
    }

    public function update(UpdateJobRequest $request, string $id): JsonResponse
    {
        try {
            $job = $this->jobService->update(
                employer: $request->user(),
                jobId:    $id,
                updateJobDataDTO:     UpdateJobDataDTO::from($request->validated()),
            );

            return $this->ok(
                ['job' => new JobResource($job)],
                'Job berhasil diperbarui.',
            );
        } catch (ApiException $e) {
            return $this->error($e->getMessage(), $e->getStatusCode(), $e->getErrors());
        }
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $this->jobService->delete($request->user(), $id);

            return $this->noContent('Job berhasil dihapus.');
        } catch (ApiException $e) {
            return $this->error($e->getMessage(), $e->getStatusCode(), $e->getErrors());
        }
    }
}
