<?php

namespace App\Exceptions;

use Exception;

class DuplicateResourceException extends Exception
{
    public function __construct(string $message = 'Resource already exists')
    {
        parent::__construct($message);
    }
}
