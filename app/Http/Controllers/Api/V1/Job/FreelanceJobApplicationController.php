<?php

namespace App\Http\Controllers\Api\V1\Job;

use App\DTOs\Job\ApplyJobDataDTO;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Job\ApplyJobRequest;
use App\Http\Resources\Api\V1\Job\JobApplicationCollection;
use App\Http\Resources\Api\V1\Job\JobApplicationResource;
use App\Services\Job\JobApplicationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FreelanceJobApplicationController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly JobApplicationService $jobApplicationService,
    ) {}

    public function apply(ApplyJobRequest $request, string $jobId): JsonResponse
    {
        try {
            $application = $this->jobApplicationService->apply(
                freelancer: $request->user(),
                jobId:      $jobId,
                applyJobDataDTO:       new ApplyJobDataDTO(
                    cv_file:      $request->file('cv_file'),
                    cover_letter: $request->input('cover_letter'),
                ),
            );

            return $this->created([
                'application' => new JobApplicationResource($application),
            ], 'Lamaran berhasil dikirim.');
        } catch (ApiException $e) {
            return $this->error($e->getMessage(), $e->getStatusCode(), $e->getErrors());
        }
    }

    public function myApplications(Request $request): JsonResponse
    {
        try {
            $applications = $this->jobApplicationService->getMyApplications(
                freelancer: $request->user(),
                perPage:    (int) $request->query('per_page', 15),
            );

            return $this->paginated(
                new JobApplicationCollection($applications),
                'Riwayat lamaran berhasil diambil.',
            );
        } catch (ApiException $e) {
            return $this->error($e->getMessage(), $e->getStatusCode(), $e->getErrors());
        }
    }
}
