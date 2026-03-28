<?php

namespace App\DTOs\Job;

use App\Enums\TypeJobEnum;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;

class JobFilterDataDTO extends Data
{
    public function __construct(
        #[Max(255)]
        public readonly ?string $search = null,

        #[Max(150)]
        public readonly ?string $location = null,

        #[Enum(TypeJobEnum::class)]
        public readonly ?TypeJobEnum $type = null,

        public readonly int $per_page = 10,
    ) {}
}
