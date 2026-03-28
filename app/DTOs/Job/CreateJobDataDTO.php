<?php

namespace App\DTOs\Job;

use App\Enums\StatusJobEnum;
use App\Enums\TypeJobEnum;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;
use Symfony\Contracts\Service\Attribute\Required;

class CreateJobDataDTO extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public readonly string $title,

        #[Required]
        public readonly string $description,

        public readonly ?string $requirements = null,

        #[Max(100)]
        public readonly ?string $salary_range = null,

        #[Max(150)]
        public readonly ?string $location = null,

        #[Required, Enum(TypeJobEnum::class)]
        public readonly TypeJobEnum $type = TypeJobEnum::FREELANCER,

        #[Required, Enum(StatusJobEnum::class)]
        public readonly StatusJobEnum $status = StatusJobEnum::DRAFT,
    ) {}
}
