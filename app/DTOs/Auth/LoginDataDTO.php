<?php

namespace App\DTOs\Auth;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Symfony\Contracts\Service\Attribute\Required;

class LoginDataDTO extends Data
{
    public function __construct(
        #[Required, Email, Max(255)]
        public readonly string $email,

        #[Required, Max(255)]
        public readonly string $password,

        /*
         * Nama token yang akan disimpan ke personal_access_tokens
         * By default 'web'
         * */
        public readonly string $token_name = 'web',
    ) {}
}
