<?php

namespace Tests\Feature;

use App\Enums\StatusJobEnum;
use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class JobModelTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_is_published_returns_true_untuk_status_published(): void
    {
        $job = Job::factory()->published()->make();
        $this->assertTrue($job->isPublished());
        $this->assertFalse($job->isDraft());
        $this->assertFalse($job->isClosed());
    }

    public function test_is_draft_returns_true_untuk_status_draft(): void
    {
        $job = Job::factory()->draft()->make();
        $this->assertTrue($job->isDraft());
        $this->assertFalse($job->isPublished());
    }

    public function test_is_closed_returns_true_untuk_status_closed(): void
    {
        $job = Job::factory()->closed()->make();
        $this->assertTrue($job->isClosed());
        $this->assertFalse($job->isPublished());
    }

    public function test_is_applicable_hanya_true_untuk_published(): void
    {
        $this->assertTrue(Job::factory()->published()->make()->isApplicable());
        $this->assertFalse(Job::factory()->draft()->make()->isApplicable());
        $this->assertFalse(Job::factory()->closed()->make()->isApplicable());
    }

    public function test_is_owned_by_return_true_untuk_employer_yang_benar(): void
    {
        $employer = $this->createEmployer();
        $job      = Job::factory()->forEmployer($employer)->published()->create();

        $otherEmployer = $this->createEmployer();

        $this->assertTrue($job->isOwnedBy($employer));
        $this->assertFalse($job->isOwnedBy($otherEmployer));
    }

    public function test_scope_published_hanya_return_job_published(): void
    {
        $employer = $this->createEmployer();

        Job::factory()->forEmployer($employer)->published()->count(3)->create();
        Job::factory()->forEmployer($employer)->draft()->count(2)->create();
        Job::factory()->forEmployer($employer)->closed()->count(1)->create();

        $published = Job::published()->get();

        $this->assertCount(3, $published);
        $published->each(fn ($job) => $this->assertEquals(StatusJobEnum::PUBLISHED, $job->status));
    }

    public function test_scope_search_menemukan_job_berdasarkan_title(): void
    {
        $employer = $this->createEmployer();

        Job::factory()->forEmployer($employer)->published()->create([
            'title'       => 'Backend Developer Laravel',
            'description' => 'Kami mencari backend developer',
        ]);

        Job::factory()->forEmployer($employer)->published()->create([
            'title'       => 'Frontend Developer React',
            'description' => 'Kami mencari frontend developer',
        ]);

        $results = Job::published()->search('laravel')->get();

        $this->assertCount(1, $results);
        $this->assertStringContainsStringIgnoringCase('laravel', $results->first()->title);
    }

    public function test_soft_delete_tidak_menghapus_record_dari_database(): void
    {
        $employer = $this->createEmployer();
        $job      = Job::factory()->forEmployer($employer)->draft()->create();
        $jobId    = $job->id;

        $job->delete();

        $this->assertNull(Job::find($jobId));
        $this->assertNotNull(Job::withTrashed()->find($jobId));
    }

    public function test_freelancer_hanya_bisa_melihat_job_published(): void
    {
        $employer = $this->createEmployer();

        Job::factory()->forEmployer($employer)->published()->count(4)->create();
        Job::factory()->forEmployer($employer)->draft()->count(3)->create();
        Job::factory()->forEmployer($employer)->closed()->count(2)->create();

        $visibleJobs = Job::published()->get();

        $this->assertCount(4, $visibleJobs);

        $visibleJobs->each(function (Job $job) {
            $this->assertTrue($job->isPublished());
            $this->assertFalse($job->isDraft());
            $this->assertFalse($job->isClosed());
        });
    }

    public function test_freelancer_tidak_dapat_melihat_job_draft(): void
    {
        $employer = $this->createEmployer();
        Job::factory()->forEmployer($employer)->draft()->count(5)->create();

        $visibleJobs = Job::published()->get();

        $this->assertCount(0, $visibleJobs);
    }

    public function test_freelancer_tidak_dapat_melihat_job_closed(): void
    {
        $employer = $this->createEmployer();
        Job::factory()->forEmployer($employer)->closed()->count(3)->create();

        $visibleJobs = Job::published()->get();

        $this->assertCount(0, $visibleJobs);
    }


    public function test_freelancer_dapat_filter_job_berdasarkan_lokasi(): void
    {
        $employer = $this->createEmployer();

        Job::factory()->forEmployer($employer)->published()->count(3)->create([
            'location' => 'Jakarta Selatan',
        ]);
        Job::factory()->forEmployer($employer)->published()->count(2)->create([
            'location' => 'Bandung',
        ]);

        $results = Job::published()->location('jakarta')->get();

        $this->assertCount(3, $results);
        $results->each(fn ($job) =>
        $this->assertStringContainsStringIgnoringCase('jakarta', $job->location)
        );
    }

    public function test_freelancer_dapat_filter_job_berdasarkan_type_freelance(): void
    {
        $employer = $this->createEmployer();

        Job::factory()->forEmployer($employer)->published()->freelance()->count(4)->create();
        Job::factory()->forEmployer($employer)->published()->partTime()->count(2)->create();

        $results = Job::published()->ofType('freelancer')->get();

        $this->assertCount(4, $results);
    }

    public function test_freelancer_dapat_filter_job_berdasarkan_type_part_time(): void
    {
        $employer = $this->createEmployer();

        Job::factory()->forEmployer($employer)->published()->freelance()->count(3)->create();
        Job::factory()->forEmployer($employer)->published()->partTime()->count(2)->create();

        $results = Job::published()->ofType('parttime')->get();

        $this->assertCount(2, $results);
    }

    public function test_search_menemukan_job_berdasarkan_description(): void
    {
        $employer = $this->createEmployer();

        Job::factory()->forEmployer($employer)->published()->create([
            'title'       => 'Software Engineer',
            'description' => 'Kami mencari ahli Kubernetes dan Docker',
        ]);

        Job::factory()->forEmployer($employer)->published()->create([
            'title'       => 'DevOps Engineer',
            'description' => 'Diperlukan pengalaman cloud AWS',
        ]);

        $results = Job::published()->search('kubernetes')->get();

        $this->assertCount(1, $results);
        $this->assertStringContainsStringIgnoringCase('kubernetes', $results->first()->description);
    }

    public function test_search_dengan_keyword_tidak_ada_return_kosong(): void
    {
        $employer = $this->createEmployer();

        Job::factory()->forEmployer($employer)->published()->count(3)->create();

        $results = Job::published()->search('xyzabctidakadakeywordini')->get();

        $this->assertCount(0, $results);
    }

    public function test_search_tidak_temukan_job_draft_meski_keyword_cocok(): void
    {
        $employer = $this->createEmployer();

        Job::factory()->forEmployer($employer)->draft()->create([
            'title'       => 'Backend Developer Laravel',
            'description' => 'Posisi untuk laravel developer',
        ]);

        // Scope published() + search() -> draft tidak boleh muncul
        $results = Job::published()->search('laravel')->get();

        $this->assertCount(0, $results);
    }

    public function test_job_published_bisa_dilamar_freelancer(): void
    {
        $job = Job::factory()->published()->make();

        $this->assertTrue($job->isApplicable());
    }

    public function test_job_draft_tidak_bisa_dilamar_freelancer(): void
    {
        $job = Job::factory()->draft()->make();

        $this->assertFalse($job->isApplicable());
    }

    public function test_job_closed_tidak_bisa_dilamar_freelancer(): void
    {
        $job = Job::factory()->closed()->make();

        $this->assertFalse($job->isApplicable());
    }

    public function test_satu_freelancer_hanya_bisa_apply_sekali_per_job(): void
    {
        $employer   = $this->createEmployer();
        $freelancer = $this->createFreelancer();
        $job        = Job::factory()->forEmployer($employer)->published()->create();

        // Apply pertama
        JobApplication::factory()->forJobAndFreelancer($job, $freelancer)->create();

        // Cek sudah apply via relasi
        $hasApplied = JobApplication::where('job_id', $job->id)
            ->where('freelancer_id', $freelancer->id)
            ->exists();

        $this->assertTrue($hasApplied);

        // Cek UNIQUE constraint via duplicate check
        $count = JobApplication::where('job_id', $job->id)
            ->where('freelancer_id', $freelancer->id)
            ->count();

        $this->assertEquals(1, $count);
    }

    public function test_freelancer_berbeda_bisa_apply_job_yang_sama(): void
    {
        $employer    = $this->createEmployer();
        $freelancer1 = $this->createFreelancer();
        $freelancer2 = $this->createFreelancer();
        $job         = Job::factory()->forEmployer($employer)->published()->create();

        JobApplication::factory()->forJobAndFreelancer($job, $freelancer1)->create();
        JobApplication::factory()->forJobAndFreelancer($job, $freelancer2)->create();

        $totalApplications = $job->applications()->count();

        $this->assertEquals(2, $totalApplications);
    }

    public function test_freelancer_bisa_apply_ke_banyak_job_berbeda(): void
    {
        $employer   = $this->createEmployer();
        $freelancer = $this->createFreelancer();

        $job1 = Job::factory()->forEmployer($employer)->published()->create();
        $job2 = Job::factory()->forEmployer($employer)->published()->create();
        $job3 = Job::factory()->forEmployer($employer)->published()->create();

        JobApplication::factory()->forJobAndFreelancer($job1, $freelancer)->create();
        JobApplication::factory()->forJobAndFreelancer($job2, $freelancer)->create();
        JobApplication::factory()->forJobAndFreelancer($job3, $freelancer)->create();

        $totalMyApplications = JobApplication::where('freelancer_id', $freelancer->id)->count();

        $this->assertEquals(3, $totalMyApplications);
    }

    public function test_job_memiliki_relasi_applications(): void
    {
        $employer   = $this->createEmployer();
        $freelancer = $this->createFreelancer();
        $job        = Job::factory()->forEmployer($employer)->published()->create();

        JobApplication::factory()->forJobAndFreelancer($job, $freelancer)->create();

        $this->assertCount(1, $job->applications);
        $this->assertInstanceOf(JobApplication::class, $job->applications->first());
    }

    public function test_job_published_at_tidak_null_saat_published(): void
    {
        $employer = $this->createEmployer();
        $job      = Job::factory()->forEmployer($employer)->published()->create();

        $this->assertNotNull($job->published_at);
    }

    public function test_job_published_at_null_saat_draft(): void
    {
        $employer = $this->createEmployer();
        $job      = Job::factory()->forEmployer($employer)->draft()->create();

        $this->assertNull($job->published_at);
    }
}
