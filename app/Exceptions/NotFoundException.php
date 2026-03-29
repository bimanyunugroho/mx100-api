<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class NotFoundException extends ApiException
{
    public function __construct(string $resource = 'Resource')
    {
        parent::__construct(
            message:    "{$resource}",
            statusCode: Response::HTTP_NOT_FOUND,
        );
    }
}
