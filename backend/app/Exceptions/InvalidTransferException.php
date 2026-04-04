<?php

namespace App\Exceptions;

use Exception;

class InvalidTransferException extends Exception
{
    public function __construct(string $message = 'Invalid transfer')
    {
        parent::__construct($message);
    }
}
