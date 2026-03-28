<?php

namespace Tests\Feature;

use App\DTOs\Job\ApplyJobDataDTO;
use App\Exceptions\ConflictException;
use App\Exceptions\NotFoundException;
use App\Models\Job;
use App\Models\User;
use App\Repositories\Contracts\Job\JobApplicationRepositoryInterface;
use App\Repositories\Contracts\Job\JobRepositoryInterface;
use App\Services\Job\JobApplicationService;
use App\Services\Shared\FileUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery\MockInterface;
use Tests\TestCase;
use Mockery;

class JobApplicationServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private JobApplicationService $service;
    private MockInterface $applicationRepository;
    private MockInterface $jobRepository;
    private MockInterface $fileUploadService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->applicationRepository = Mockery::mock(JobApplicationRepositoryInterface::class);
        $this->jobRepository         = Mockery::mock(JobRepositoryInterface::class);
        $this->fileUploadService     = Mockery::mock(FileUploadService::class);

        $this->service = new JobApplicationService(
            $this->applicationRepository,
            $this->jobRepository,
            $this->fileUploadService,
        );
    }

    public function test_apply_berhasil_untuk_job_published(): void
    {
        $freelancer = User::factory()->freelancer()->make(['id' => 'freelancer-ulid']);
        $job        = Job::factory()->published()->make(['id' => 'job-ulid']);
        $file       = UploadedFile::fake()->create('cv.pdf', 500, 'application/pdf');
        $data       = new ApplyJobDataDTO(cv_file: $file, cover_letter: 'Saya tertarik.');

        $this->jobRepository
            ->shouldReceive('findPublished')
            ->once()
            ->with('job-ulid')
            ->andReturn($job);

        $this->applicationRepository
            ->shouldReceive('hasApplied')
            ->once()
            ->with($freelancer, $job)
            ->andReturn(false);

        $this->fileUploadService
            ->shouldReceive('storeCv')
            ->once()
            ->andReturn('cvs/freelancer-ulid/cv.pdf');

        $this->applicationRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn(new \App\Models\JobApplication());

        $this->service->apply($freelancer, 'job-ulid', $data);

        $this->assertTrue(true);
    }

    public function test_apply_gagal_jika_job_tidak_published(): void
    {
        $this->expectException(NotFoundException::class);

        $freelancer = User::factory()->freelancer()->create();
        $file       = UploadedFile::fake()->create('cv.pdf', 500, 'application/pdf');
        $data       = new ApplyJobDataDTO(cv_file: $file);

        $this->jobRepository
            ->shouldReceive('findPublished')
            ->once()
            ->andReturn(null); // job tidak ditemukan atau bukan published

        $this->applicationRepository->shouldNotReceive('hasApplied');
        $this->fileUploadService->shouldNotReceive('storeCv');

        $this->service->apply($freelancer, 'job-id', $data);
    }

    public function test_apply_gagal_jika_sudah_pernah_melamar(): void
    {
        $this->expectException(ConflictException::class);

        $freelancer = User::factory()->freelancer()->create();
        $job        = Job::factory()->published()->create();
        $file       = UploadedFile::fake()->create('cv.pdf', 500, 'application/pdf');
        $data       = new ApplyJobDataDTO(cv_file: $file);

        $this->jobRepository
            ->shouldReceive('findPublished')
            ->once()
            ->andReturn($job);

        $this->applicationRepository
            ->shouldReceive('hasApplied')
            ->once()
            ->andReturn(true); // sudah apply sebelumnya

        $this->fileUploadService->shouldNotReceive('storeCv');
        $this->applicationRepository->shouldNotReceive('create');

        $this->service->apply($freelancer, 'job-id', $data);
    }

    public function test_get_job_applications_gagal_jika_job_bukan_milik_employer(): void
    {
        $this->expectException(NotFoundException::class);

        $employer = User::factory()->employer()->create();

        $this->jobRepository
            ->shouldReceive('findByEmployer')
            ->once()
            ->andReturn(null); // bukan milik employer ini

        $this->service->getJobApplications($employer, 'job-orang-lain');
    }

    public function test_get_cv_gagal_jika_application_bukan_milik_employer(): void
    {
        $this->expectException(NotFoundException::class);

        $employer = User::factory()->employer()->create();

        $this->applicationRepository
            ->shouldReceive('findForEmployer')
            ->once()
            ->andReturn(null);

        $this->service->getCvForDownload($employer, 'application-id');
    }

    protected function test_tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
