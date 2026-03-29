<?php

namespace App\Http\Controllers\Api\V1\Job;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Job\JobApplicationCollection;
use App\Http\Resources\Api\V1\Job\JobApplicationResource;
use App\Services\Job\JobApplicationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EmployerJobApplicationController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly JobApplicationService $jobApplicationService,
    ) {}

    public function index(Request $request, string $jobId): JsonResponse
    {
        try {
            $applications = $this->jobApplicationService->getJobApplications(
                employer: $request->user(),
                jobId:    $jobId,
                perPage:  (int) $request->query('per_page', 15),
            );

            return $this->paginated(
                new JobApplicationCollection($applications),
                'Daftar pelamar berhasil diambil.',
            );
        } catch (ApiException $e) {
            return $this->error($e->getMessage(), $e->getStatusCode(), $e->getErrors());
        }
    }

    public function show(Request $request, string $applicationId): JsonResponse
    {
        try {
            $application = $this->jobApplicationService->getApplicationDetail(
                employer:       $request->user(),
                applicationId: $applicationId,
            );

            return $this->ok([
                'application' => new JobApplicationResource($application),
            ]);
        } catch (ApiException $e) {
            return $this->error($e->getMessage(), $e->getStatusCode(), $e->getErrors());
        }
    }

    public function downloadCv(Request $request, string $applicationId): BinaryFileResponse|JsonResponse
    {
        try {
            $cv = $this->jobApplicationService->getCvForDownload(
                employer:      $request->user(),
                applicationId: $applicationId,
            );

            return response()->download(
                file:    $cv['path'],
                name:    $cv['original_name'],
                headers: ['Content-Type' => $cv['mime_type']],
            );
        } catch (ApiException $e) {
            return $this->error($e->getMessage(), $e->getStatusCode(), $e->getErrors());
        }
    }
}
