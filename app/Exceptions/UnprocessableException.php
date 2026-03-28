<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class UnprocessableException extends ApiException
{
    public function __construct(string $message, array $errors = [])
    {
        parent::__construct(
            message:    $message,
            statusCode: Response::HTTP_UNPROCESSABLE_ENTITY,
            errors:     $errors,
        );
    }
}
