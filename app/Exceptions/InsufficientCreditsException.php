<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientCreditsException extends RuntimeException
{
    public function __construct(string $message = 'Nicht genügend Credits vorhanden.')
    {
        parent::__construct($message);
    }
}
