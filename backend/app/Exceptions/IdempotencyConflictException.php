<?php

namespace App\Exceptions;

use Exception;

class IdempotencyConflictException extends Exception
{
    public function __construct(string $message = 'Idempotency conflict')
    {
        parent::__construct($message);
    }
}
