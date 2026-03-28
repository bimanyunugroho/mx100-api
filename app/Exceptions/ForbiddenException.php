<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class ForbiddenException extends ApiException
{
    public function __construct(string $message = 'Akses ditolak.')
    {
        parent::__construct(
            message:    $message,
            statusCode: Response::HTTP_FORBIDDEN,
        );
    }
}
