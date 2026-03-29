<?php

namespace Database\Seeders;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Enums\RoleUserEnum;
use App\Enums\StatusJobEnum;

class JobApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $publishedJobs = Job::where('status', StatusJobEnum::PUBLISHED)->get();
        $freelancers   = User::where('role', RoleUserEnum::FREELANCER)->get();

        $applicationCount = 0;

        foreach ($publishedJobs as $job) {
            $applicantCount = min(rand(2, 5), $freelancers->count());
            $selectedFreelancers = $freelancers->shuffle()->take($applicantCount);

            foreach ($selectedFreelancers as $freelancer) {
                JobApplication::factory()
                    ->forJobAndFreelancer($job, $freelancer)
                    ->create();

                $applicationCount++;
            }
        }
    }
}
