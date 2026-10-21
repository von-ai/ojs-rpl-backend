<?php

namespace App\Exceptions;

class IdentityException extends ApiException
{
    public function __construct(int $status, string $errorCode, string $message, array $errors = [])
    {
        parent::__construct($status, $errorCode, $message, $errors);
    }
}
