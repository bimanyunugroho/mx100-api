<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class ConflictException extends ApiException
{
    public function __construct(string $message)
    {
        parent::__construct(
            message:    $message,
            statusCode: Response::HTTP_CONFLICT,
        );
    }
}
