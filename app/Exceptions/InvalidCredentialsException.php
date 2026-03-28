<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class InvalidCredentialsException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            message:    'Email atau password salah.',
            statusCode: Response::HTTP_UNAUTHORIZED,
        );
    }
}
