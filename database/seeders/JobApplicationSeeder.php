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
        // Ambil hanya job published aja
        // setiap freelancer hanya bisa apply ke job yang published
        $publishedJobs = Job::where('status', StatusJobEnum::PUBLISHED)->get();
        $freelancers   = User::where('role', RoleUserEnum::FREELANCER)->get();

        $applicationCount = 0;

        foreach ($publishedJobs as $job) {
            // Setiap job published mendapat 2 – 5 pelamar secara acak
            $applicantCount = min(rand(2, 5), $freelancers->count());

            // Ambil freelancer secara acak tanpa duplikat
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
