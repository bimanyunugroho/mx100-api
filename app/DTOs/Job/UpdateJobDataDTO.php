<?php

namespace App\DTOs\Job;

use App\Enums\StatusJobEnum;
use App\Enums\TypeJobEnum;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;

class UpdateJobDataDTO extends Data
{
    public function __construct(
        #[Max(255)]
        public readonly ?string $title = null,

        public readonly ?string $description = null,

        public readonly ?string $requirements = null,

        #[Max(100)]
        public readonly ?string $salary_range = null,

        #[Max(150)]
        public readonly ?string $location = null,

        #[Enum(TypeJobEnum::class)]
        public readonly ?TypeJobEnum $type = null,

        #[Enum(StatusJobEnum::class)]
        public readonly ?StatusJobEnum $status = null,
    ) {}
}
