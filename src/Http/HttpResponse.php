<?php

declare(strict_types=1);

namespace KandMailer\Http;

class HttpResponse
{
    public function __construct(
        private readonly string $body,
        private readonly int $statusCode
    ) {
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }
}

