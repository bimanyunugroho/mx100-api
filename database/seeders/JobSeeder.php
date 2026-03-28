<?php

namespace Database\Seeders;

use App\Enums\StatusJobEnum;
use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Enums\RoleUserEnum;

class JobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employers = User::where('role', RoleUserEnum::EMPLOYER)->get();

        foreach ($employers as $employer) {
            // Setiap employer punya campuran draft, published, dan closed
            Job::factory()
                ->forEmployer($employer)
                ->published()
                ->count(2)
                ->create();

            Job::factory()
                ->forEmployer($employer)
                ->draft()
                ->count(1)
                ->create();

            Job::factory()
                ->forEmployer($employer)
                ->closed()
                ->count(1)
                ->create();
        }

        $total     = Job::count();
        $published = Job::where('status', StatusJobEnum::PUBLISHED)->count();
        $draft     = Job::where('status', StatusJobEnum::DRAFT)->count();
        $closed    = Job::where('status', StatusJobEnum::CLOSED)->count();
    }
}
