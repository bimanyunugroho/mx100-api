<?php

namespace App\Services\Job;

use App\DTOs\Job\CreateJobDataDTO;
use App\DTOs\Job\JobFilterDataDTO;
use App\DTOs\Job\UpdateJobDataDTO;
use App\Enums\StatusJobEnum;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnprocessableException;
use App\Models\Job;
use App\Models\User;
use App\Repositories\Contracts\Job\JobRepositoryInterface;
use Illuminate\Support\Facades\DB;

class JobService
{
    public function __construct(
        private readonly JobRepositoryInterface $jobRepository
    ) {}

    /**
     * Buat job baru.
     * status hanya boleh draft atau published saat create.
     * published_at di-set di sini jika status = published.
     *
     * @throws UnprocessableException
     */
    public function create(User $employer, CreateJobDataDTO $createJobDataDTO): Job
    {
        if ($createJobDataDTO->status === StatusJobEnum::CLOSED) {
            throw new UnprocessableException(
                'Lowongan Pekerjaan ini tidak bisa dibuat langsung dengan status closed.'
            );
        }

        // Set published_at di service jika status published
        $publishedAt = $createJobDataDTO->status === StatusJobEnum::PUBLISHED ? now() : null;

        return DB::transaction(function () use ($employer, $createJobDataDTO, $publishedAt): Job {
            $job = $this->jobRepository->create($employer, $createJobDataDTO);

            if ($publishedAt) {
                $job->update(['published_at' => $publishedAt]);
            }

            return $job->fresh();
        });
    }

    /**
     * Update job milik employer.
     *
     * - Ownership check: hanya pemilik job yang bisa update
     * - Job closed tidak bisa di-update kembali ke draft/published
     * - published_at dikelola di sini berdasarkan perubahan status
     *
     * @throws NotFoundException
     * @throws ForbiddenException
     * @throws UnprocessableException
     */
    public function update(User $employer, string $jobId, UpdateJobDataDTO $updateJobDataDTO): Job
    {
        $job = $this->jobRepository->findByEmployer($jobId, $employer);

        if (!$job) {
            throw new NotFoundException('Lowongan Pekerjaan Tidak Ditemukan');
        }

        // job closed tidak bisa di-reopen via update biasa
        if ($job->isClosed() && $updateJobDataDTO->status !== StatusJobEnum::CLOSED) {
            throw new UnprocessableException(
                'Lowongan Pekerjaan yang sudah closed tidak bisa diubah statusnya kembali.'
            );
        }

        // Handle published_at berdasarkan perubahan status
        $this->applyPublishedAt($job, $updateJobDataDTO);

        return DB::transaction(function () use ($job, $updateJobDataDTO): Job {
            return $this->jobRepository->update($job, $updateJobDataDTO);
        });
    }

    /**
     * Soft-delete job.
     * hanya job berstatus draft yang boleh dihapus.
     * Job published harus di-close dulu sebelum bisa dihapus.
     *
     * @throws NotFoundException
     * @throws UnprocessableException
     */
    public function delete(User $employer, string $jobId): void
    {
        $job = $this->jobRepository->findByEmployer($jobId, $employer);

        if (!$job) {
            throw new NotFoundException('Lowongan Pekerjaan Tidak Ditemukan');
        }

        // hanya draft yang boleh dihapus
        if (!$job->isDraft()) {
            throw new UnprocessableException(
                'Hanya Lowongan Pekerjaan yang berstatus draft yang dapat dihapus. '
                . 'Ubah status ke closed terlebih dahulu.'
            );
        }

        $this->jobRepository->delete($job);
    }

    /**
     * Ambil semua job milik employer (semua status) + paginated.
     */
    public function getEmployerJobs(User $employer, int $perPage = 15): LengthAwarePaginator
    {
        return $this->jobRepository->getByEmployer($employer, $perPage);
    }


    /**
     * Ambil detail job milik employer.
     *
     * @throws NotFoundException
     */
    public function getEmployerJob(User $employer, string $jobId): Job
    {
        $job = $this->jobRepository->findByEmployer($jobId, $employer);

        if (!$job) {
            throw new NotFoundException('Lowongan Pekerjaan Tidak Ditemukan');
        }

        return $job->loadCount('applications');
    }

    /**
     * Ambil list job published untuk freelancer, dengan filter opsional.
     */
    public function getPublishedJobs(JobFilterDataDTO $jobFilterDataDTO): LengthAwarePaginator
    {
        return $this->jobRepository->getPublished($jobFilterDataDTO);
    }

    /**
     * Ambil detail job published untuk freelancer.
     *
     * @throws NotFoundException
     */
    public function getPublishedJob(string $jobId): Job
    {
        $job = $this->jobRepository->findPublished($jobId);

        if (!$job) {
            throw new NotFoundException('Lowongan Pekerjaan Tidak Ditemukan');
        }

        return $job;
    }

    /**
     * Atur published_at berdasarkan perubahan status.
     */
    private function applyPublishedAt(Job $job, UpdateJobDataDTO $updateJobDataDTO): void
    {
        if (!$updateJobDataDTO->status) {
            return;
        }

        // Baru pertama kali di-publish langsung sikat set published_at
        if ($updateJobDataDTO->status === StatusJobEnum::PUBLISHED && ! $job->published_at) {
            $job->published_at = now();
        }

        // Ngga peduli di unpublish atau diclose langsung reset si published_at nya
        if (in_array($updateJobDataDTO->status, [StatusJobEnum::DRAFT, StatusJobEnum::CLOSED], strict: true)) {
            $job->published_at = null;
        }
    }
}
