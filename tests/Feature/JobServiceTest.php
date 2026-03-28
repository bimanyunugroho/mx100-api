<?php

namespace Tests\Feature;

use App\DTOs\Job\CreateJobDataDTO;
use App\DTOs\Job\UpdateJobDataDTO;
use App\Enums\StatusJobEnum;
use App\Enums\TypeJobEnum;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnprocessableException;
use App\Models\Job;
use App\Models\User;
use App\Repositories\Contracts\Job\JobRepositoryInterface;
use App\Services\Job\JobService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery\MockInterface;
use Tests\TestCase;
use Mockery;

class JobServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    private JobService $jobService;
    private MockInterface $jobRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jobRepository = Mockery::mock(JobRepositoryInterface::class);
        $this->jobService    = new JobService($this->jobRepository);
    }

    public function test_create_job_berhasil_dengan_status_draft(): void
    {
        $employer = User::factory()->employer()->create();
        $data     = new CreateJobDataDTO(
            title:       'Backend Developer',
            description: 'Deskripsi pekerjaan',
            type:        TypeJobEnum::FREELANCER,
            status:      StatusJobEnum::DRAFT,
        );

        $job = Job::factory()->draft()->create();

        $this->jobRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($job);

        $result = $this->jobService->create($employer, $data);

        $this->assertInstanceOf(Job::class, $result);
    }

    public function test_create_job_berhasil_dengan_status_published(): void
    {
        $employer = User::factory()->employer()->create();
        $data     = new CreateJobDataDTO(
            title:       'Frontend Developer',
            description: 'Deskripsi pekerjaan',
            type:        TypeJobEnum::FREELANCER,
            status:      StatusJobEnum::PUBLISHED,
        );

        $job = Job::factory()->published()->create();

        $this->jobRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($job);

        $result = $this->jobService->create($employer, $data);

        $this->assertInstanceOf(Job::class, $result);
    }

    public function test_create_job_gagal_jika_status_closed(): void
    {
        $this->expectException(UnprocessableException::class);

        $employer = User::factory()->employer()->create();
        $data     = new CreateJobDataDTO(
            title:       'Job Test',
            description: 'Deskripsi',
            type:        TypeJobEnum::FREELANCER,
            status:      StatusJobEnum::CLOSED,
        );

        $this->jobRepository->shouldNotReceive('create');

        $this->jobService->create($employer, $data);
    }

    public function test_update_job_berhasil_oleh_pemiliknya(): void
    {
        $employer = User::factory()->employer()->create();
        $job      = Job::factory()->draft()->create();
        $data     = new UpdateJobDataDTO(title: 'Judul Baru');

        $this->jobRepository
            ->shouldReceive('findByEmployer')
            ->once()
            ->andReturn($job);

        $this->jobRepository
            ->shouldReceive('update')
            ->once()
            ->andReturn($job);

        $result = $this->jobService->update($employer, 'job-id', $data);

        $this->assertInstanceOf(Job::class, $result);
    }

    public function test_update_job_gagal_jika_job_tidak_ditemukan(): void
    {
        $this->expectException(NotFoundException::class);

        $employer = User::factory()->employer()->create();
        $data     = new UpdateJobDataDTO(title: 'Judul Baru');

        $this->jobRepository
            ->shouldReceive('findByEmployer')
            ->once()
            ->andReturn(null);

        $this->jobService->update($employer, 'job-tidak-ada', $data);
    }

    public function test_update_job_gagal_jika_job_sudah_closed(): void
    {
        $this->expectException(UnprocessableException::class);

        $employer = User::factory()->employer()->create();
        $job      = Job::factory()->closed()->create();
        $data     = new UpdateJobDataDTO(status: StatusJobEnum::DRAFT);

        $this->jobRepository
            ->shouldReceive('findByEmployer')
            ->once()
            ->andReturn($job);

        $this->jobRepository->shouldNotReceive('update');

        $this->jobService->update($employer, 'job-id', $data);
    }

    public function test_delete_job_berhasil_jika_status_draft(): void
    {
        $employer = User::factory()->employer()->create();
        $job      = Job::factory()->draft()->create();

        $this->jobRepository
            ->shouldReceive('findByEmployer')
            ->once()
            ->andReturn($job);

        $this->jobRepository
            ->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        $this->jobService->delete($employer, 'job-id');
        $this->assertTrue(true);
    }

    public function test_delete_job_gagal_jika_status_published(): void
    {
        $this->expectException(UnprocessableException::class);

        $employer = User::factory()->employer()->create();
        $job      = Job::factory()->published()->create();

        $this->jobRepository
            ->shouldReceive('findByEmployer')
            ->once()
            ->andReturn($job);

        $this->jobRepository->shouldNotReceive('delete');

        $this->jobService->delete($employer, 'job-id');
    }

    public function test_delete_job_gagal_jika_status_closed(): void
    {
        $this->expectException(UnprocessableException::class);

        $employer = User::factory()->employer()->create();
        $job      = Job::factory()->closed()->create();

        $this->jobRepository
            ->shouldReceive('findByEmployer')
            ->once()
            ->andReturn($job);

        $this->jobService->delete($employer, 'job-id');
    }

    public function test_get_published_job_gagal_jika_tidak_ditemukan(): void
    {
        $this->expectException(NotFoundException::class);

        $this->jobRepository
            ->shouldReceive('findPublished')
            ->once()
            ->andReturn(null);

        $this->jobService->getPublishedJob('job-tidak-ada');
    }

    protected function test_tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
