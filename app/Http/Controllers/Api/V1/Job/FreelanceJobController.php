<?php

namespace App\Http\Controllers\Api\V1\Job;

use App\DTOs\Job\JobFilterDataDTO;
use App\Enums\TypeJobEnum;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Job\JobCollection;
use App\Http\Resources\Api\V1\Job\JobResource;
use App\Services\Job\JobService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FreelanceJobController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly JobService $jobService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filter = JobFilterDataDTO::from([
                'search'   => $request->query('filter.search') ?? $request->query('search'),
                'location' => $request->query('filter.location') ?? $request->query('location'),
                'type'     => $request->query('filter.type') ? TypeJobEnum::from($request->query('filter.type')) : null,
                'per_page' => (int) $request->query('per_page', 10),
            ]);

            $jobs = $this->jobService->getPublishedJobs($filter);

            return $this->paginated(
                new JobCollection($jobs),
                'Daftar lowongan berhasil diambil.',
            );
        } catch (ApiException $e) {
            return $this->error($e->getMessage(), $e->getStatusCode(), $e->getErrors());
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $job = $this->jobService->getPublishedJob($id);

            return $this->ok([
                'job' => new JobResource($job),
            ]);
        } catch (ApiException $e) {
            return $this->error($e->getMessage(), $e->getStatusCode(), $e->getErrors());
        }
    }
}
