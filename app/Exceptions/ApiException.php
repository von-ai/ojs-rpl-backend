<?php

namespace App\Exceptions;

use RuntimeException;

class ApiException extends RuntimeException
{
    protected array $headers = [];

    protected array $errors = [];

    public function __construct(
        public readonly int $status,
        public readonly string $errorCode,
        string $message,
        array $errors = []
    ) {
        parent::__construct($message);
        $this->errors = $errors;
    }

    public function withHeaders(array $headers): static
    {
        $this->headers = $headers;

        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
