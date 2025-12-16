<?php

declare(strict_types=1);

namespace KandMailer\Http;

use RuntimeException;

class CurlHttpClient implements HttpClientInterface
{
    public function __construct(
        private readonly int $timeout = 10
    ) {
    }

    public function request(
        string $method,
        string $url,
        array $headers,
        ?string $body = null
    ): HttpResponse {
        $ch = curl_init();

        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];

        if ($body !== null) {
            $options[CURLOPT_POSTFIELDS] = $body;
        }

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('cURL error: ' . $error);
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return new HttpResponse($response, $statusCode);
    }
}

