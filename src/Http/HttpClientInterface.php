<?php

declare(strict_types=1);

namespace KandMailer\Http;

interface HttpClientInterface
{
    /**
     * Execute an HTTP request.
     *
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $url Full URL to request
     * @param array<string,string> $headers HTTP headers
     * @param string|null $body Request body
     * @return HttpResponse
     * @throws \RuntimeException On request failure
     */
    public function request(
        string $method,
        string $url,
        array $headers,
        ?string $body = null
    ): HttpResponse;
}

