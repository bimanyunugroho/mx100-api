<?php

namespace App\Services\Job;

use App\DTOs\Job\ApplyJobDataDTO;
use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnprocessableException;
use App\Models\JobApplication;
use App\Models\User;
use App\Repositories\Contracts\Job\JobApplicationRepositoryInterface;
use App\Repositories\Contracts\Job\JobRepositoryInterface;
use App\Services\Shared\FileUploadService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JobApplicationService
{
    public function __construct(
        private readonly JobApplicationRepositoryInterface $jobApplicationRepository,
        private readonly JobRepositoryInterface $jobRepository,
        private readonly FileUploadService $fileUploadService
    ) {}

    /**
     * Submit lamaran CV ke job.
     * - Job harus berstatus published
     * - Freelancer belum pernah apply ke job yang sama
     * - File CV wajib ada, max 5MB, format PDF
     *
     * @throws NotFoundException
     * @throws UnprocessableException
     * @throws ConflictException
     */
    public function apply(User $freelancer, string $jobId, ApplyJobDataDTO $applyJobDataDTO): JobApplication
    {
        $job = $this->jobRepository->findPublished($jobId);

        if (!$job) {
            throw new NotFoundException('Lowongan Pekerjaan Tidak Ditemukan');
        }

        if (!$job->isApplicable()) {
            throw new UnprocessableException(
                'Data tidak valid',
                ['status' => ['Lowongan Pekerjaan ini tidak dapat dilamar. Hanya Lowongan Pekerjaan dengan status published yang bisa dilamar.']]
            );
        }

        if ($this->jobApplicationRepository->hasApplied($freelancer, $job)) {
            throw new ConflictException(
                'Kamu sudah pernah melamar ke lowongan pekerjaan ini. Setiap freelancer hanya bisa mengirim satu lamaran per lowongan pekerjaan.'
            );
        }

        $cvPath = $this->fileUploadService->storeCv($applyJobDataDTO->cv_file, $freelancer->id);

        try {
            $application = DB::transaction(function () use ($freelancer, $job, $applyJobDataDTO, $cvPath): JobApplication {
                return $this->jobApplicationRepository->create([
                    'job_id'           => $job->id,
                    'freelancer_id'    => $freelancer->id,
                    'cover_letter'     => $applyJobDataDTO->cover_letter,
                    'cv_path'          => $cvPath,
                    'cv_original_name' => $applyJobDataDTO->cv_file->getClientOriginalName(),
                    'cv_mime_type'     => $applyJobDataDTO->cv_file->getMimeType(),
                    'cv_size_bytes'    => $applyJobDataDTO->cv_file->getSize(),
                ]);
            });
        } catch (\Throwable $e) {
            $this->fileUploadService->deleteCv($cvPath);
            throw $e;
        }

        return $application->load(['job', 'employer']);
    }

    /**
     * Semua lamaran yang pernah dikirim freelancer, paginated.
     */
    public function getMyApplications(User $freelancer, int $perPage = 15): LengthAwarePaginator
    {
        return $this->jobApplicationRepository->getByFreelancer($freelancer, $perPage);
    }

    /**
     * Semua lamaran untuk sebuah job milik employer + paginated.
     * employer hanya bisa lihat lamaran job miliknya.
     *
     * @throws NotFoundException
     * @throws ForbiddenException
     */
    public function getJobApplications(User $employer, string $jobId, int $perPage = 15): LengthAwarePaginator
    {
        $job = $this->jobRepository->findByEmployer($jobId, $employer);

        if (!$job) {
            throw new NotFoundException('Lowongan Pekerjaan Tidak Ditemukan');
        }

        return $this->jobApplicationRepository->getByJob($job, $perPage);
    }

    /**
     * Ambil path CV untuk di-download employer.
     * employer hanya bisa akses CV dari job miliknya.
     *
     * @return array{ path: string, original_name: string, mime_type: string }
     * @throws NotFoundException
     * @throws ForbiddenException
     */
    public function getCvForDownload(User $employer, string $applicationId): array
    {
        $application = $this->jobApplicationRepository->findForEmployer($applicationId, $employer);

        if (!$application) {
            throw new NotFoundException('Lamaran Pekerjaan Tidak Ditemukan');
        }

        $fullPath = $this->fileUploadService->getCvPath($application->cv_path);

        if (!$fullPath) {
            throw new NotFoundException('File CV Tidak Ditemukan');
        }

        return [
            'path'          => $fullPath,
            'original_name' => $application->cv_original_name,
            'mime_type'     => $application->cv_mime_type,
        ];
    }

    /**
     * Detail satu lamaran — employer view.
     *
     * @throws NotFoundException
     */
    public function getApplicationDetail(User $employer, string $applicationId): JobApplication
    {
        $application = $this->jobApplicationRepository->findForEmployer($applicationId, $employer);

        if (! $application) {
            throw new NotFoundException('Lamaran Pekerjaan Tidak Ditemukan');
        }

        return $application;
    }
}
