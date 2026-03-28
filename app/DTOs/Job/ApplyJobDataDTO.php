<?php

namespace App\DTOs\Job;

use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Data;

class ApplyJobDataDTO extends Data
{
    public function __construct(
        public readonly UploadedFile $cv_file,
        public readonly ?string      $cover_letter = null,
    ) {}
}
