<?php

namespace App\DTOs\Auth;

use App\Enums\RoleUserEnum;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Symfony\Contracts\Service\Attribute\Required;

class RegisterDataDTO extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public readonly string $name,

        #[Required, Email, Max(255), Unique('users', 'email')]
        public readonly string $email,

        #[Required, Min(8), Max(255)]
        public readonly string $password,

        #[Required, Enum(RoleUserEnum::class)]
        public readonly RoleUserEnum $role,

        #[Max(255)]
        public readonly ?string $company_name = null,

        #[Max(20)]
        public readonly ?string $phone = null,
    ) {}
}
