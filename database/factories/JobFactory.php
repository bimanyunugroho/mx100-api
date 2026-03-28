<?php

namespace Database\Factories;

use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\StatusJobEnum;
use App\Enums\TypeJobEnum;

/**
 * @extends Factory<Job>
 */
class JobFactory extends Factory
{
    protected $model = Job::class;

    private array $jobTitles = [
        'Fullstack Web Developer Laravel',
        'Frontend Developer React',
        'UI/UX Designer',
        'Mobile Developer Flutter',
        'Backend Developer Java',
        'Data Analyst Python',
        'QA Engineer Automation',
        'Manual Tester',
        'Business Analyst',
        'Project Manager IT',
        'DevOps GCP'
    ];

    private array $locations = [
        'Jakarta Selatan',
        'Jakarta Pusat',
        'Jakarta Barat',
        'Bandung',
        'Surabaya',
        'Depok',
        'Remote',
        'Hybrid - Jakarta Selatan',
        'Hybrid - Bandung'
    ];

    private array $requirements = [
        "- Pengalaman minimal 1 tahun di bidang terkait\n- Mampu bekerja mandiri dan dalam tim\n- Komunikasi yang baik\n- Portofolio yang relevan",
        "- Menguasai tools yang disebutkan\n- Pengalaman freelance minimal 6 bulan\n- Responsif dan tepat waktu\n- Bersedia revisi sesuai brief",
        "- Fresh graduate dipersilakan melamar\n- Memiliki portofolio atau project pribadi\n- Bersedia onboarding online\n- Komitmen terhadap deadline",
        "- Pengalaman minimal 2 tahun\n- Pernah menangani project skala menengah\n- Mampu estimasi waktu pengerjaan\n- Familiar dengan metodologi Agile",
        "- Menguasai bahasa Indonesia dengan baik\n- Kemampuan riset yang kuat\n- Deadline-oriented",
    ];

    public function definition(): array
    {
        $status      = fake()->randomElement(StatusJobEnum::cases());
        $publishedAt = $status === StatusJobEnum::PUBLISHED
            ? fake()->dateTimeBetween('-30 days', 'now')
            : null;

        $title = fake()->randomElement($this->jobTitles);

        return [
            'employer_id'  => User::factory()->employer(),
            'title'        => $title,
            'description'  => $this->generateDescription($title),
            'requirements' => fake()->randomElement($this->requirements),
            'salary_range' => $this->generateSalaryRange(),
            'location'     => fake()->randomElement($this->locations),
            'type'         => fake()->randomElement(TypeJobEnum::cases()),
            'status'       => $status,
            'published_at' => $publishedAt,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status'       => StatusJobEnum::DRAFT,
            'published_at' => null,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status'       => StatusJobEnum::PUBLISHED,
            'published_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn () => [
            'status'       => StatusJobEnum::CLOSED,
            'published_at' => fake()->dateTimeBetween('-60 days', '-31 days'),
        ]);
    }

    public function forEmployer(User $employer): static
    {
        return $this->state(fn () => [
            'employer_id' => $employer->id,
        ]);
    }

    public function freelance(): static
    {
        return $this->state(fn () => [
            'type' => TypeJobEnum::FREELANCER,
        ]);
    }

    public function partTime(): static
    {
        return $this->state(fn () => [
            'type' => TypeJobEnum::PARTTIME,
        ]);
    }

    private function generateDescription(string $title): string
    {
        $intro = fake()->randomElement([
            'Kami membuka kesempatan bagi',
            'Dibutuhkan segera',
            'Bergabunglah bersama tim kami sebagai',
            'Kami mencari kandidat terbaik untuk posisi',
        ]);

        return "{$intro} {$title} yang berpengalaman dan berdedikasi tinggi.\n\n"
            . "Deskripsi Pekerjaan:\n"
            . fake()->paragraphs(2, true) . "\n\n"
            . "Yang akan kamu kerjakan:\n"
            . "- " . implode("\n- ", fake()->sentences(4));
    }

    private function generateSalaryRange(): string
    {
        $ranges = [
            '500.000 - 1.000.000',
            '1.000.000 - 2.000.000',
            '2.000.000 - 3.500.000',
            '3.500.000 - 5.000.000',
            '5.000.000 - 8.000.000',
            '8.000.000 - 12.000.000',
            '12.000.000 - 20.000.000',
            'Negotiable'
        ];

        return fake()->randomElement($ranges);
    }
}
