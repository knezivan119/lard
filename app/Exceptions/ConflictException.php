<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class ConflictException extends HttpException
{
    public function __construct( string $message = 'Conflict', ?Throwable $previous = null, array $headers = [ ] )
    {
        parent::__construct( 409, $message, $previous, $headers );
    }
}

