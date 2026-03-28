<?php

namespace Database\Factories;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

/**
 * @extends Factory<JobApplication>
 */
class JobApplicationFactory extends Factory
{
    protected $model = JobApplication::class;

    private array $coverLetters = [
        "Dengan hormat,\n\nSaya tertarik dengan posisi yang Bapak/Ibu tawarkan. \n\nTerima kasih atas kesempatan ini.",
        "Halo,\n\nSaya menemukan lowongan ini dan sangat antusias untuk melamar. \n\nSalam,",
        "Kepada Tim HRD,\n\nMelalui surat ini saya ingin menyampaikan ketertarikan saya pada posisi yang tersedia. \n\nHormat saya,",
        "Halo Tim,\n\nSaya seorang profesional dengan pengalaman di bidang ini. \n\nSalam hangat,",
        null,
        null,
    ];

    public function definition(): array
    {
        $files = collect(Storage::disk('public')->files('cv'))
            ->filter(fn ($file) => str_ends_with($file, '.pdf'))
            ->values()
            ->all();

        $path = fake()->randomElement($files);
        $filename = basename($path);

        return [
            'job_id'           => Job::factory()->published(),
            'freelancer_id'    => User::factory()->freelancer(),
            'cover_letter'     => fake()->randomElement($this->coverLetters),
            'cv_path'          => $path,
            'cv_original_name' => $filename,
            'cv_mime_type'     => 'application/pdf',
            'cv_size_bytes'    => Storage::disk('public')->size($path),
        ];
    }

    public function forJobAndFreelancer(Job $job, User $freelancer): static
    {
        return $this->state(fn () => [
            'job_id'        => $job->id,
            'freelancer_id' => $freelancer->id,
        ]);
    }

    public function withCoverLetter(): static
    {
        return $this->state(fn () => [
            'cover_letter' => fake()->randomElement(array_filter($this->coverLetters)),
        ]);
    }

    public function withoutCoverLetter(): static
    {
        return $this->state(fn () => [
            'cover_letter' => null,
        ]);
    }
}
