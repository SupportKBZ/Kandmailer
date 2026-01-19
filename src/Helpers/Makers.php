<?php

declare(strict_types=1);

namespace KandMailer\Helpers;

use RuntimeException;

use KandMailer\MailerClient;
use KandMailer\Models\File;
use KandMailer\Models\RecipientData;

class Makers
{
    public function __construct(
        private readonly MailerClient $client,
        private readonly ?string $method = null,
        private readonly ?string $path = null
    ) {}

    /**
     * Send a request for a single recipient.
     *
     * @throws RuntimeException When the API responds with an error or invalid JSON.
     *
     * @return string The successful API response body.
     */
    public static function requestSingle(MailerClient $client, string $path, string $method = 'POST'): string
    {
        return (new self($client, $method, $path))->executeSingle();
    }

    /**
     * Send a request for multiple recipients.
     *
     * @throws RuntimeException When the API responds with an error or invalid JSON.
     *
     * @return string The successful API response body.
     */
    public static function requestMultiple(MailerClient $client, string $path, string $method = 'POST'): string
    {
        return (new self($client, $method, $path))->executeMultiple();
    }

    /**
     * Execute request for a single recipient.
     *
     * @throws RuntimeException When the API responds with an error or invalid JSON.
     *
     * @return string The successful API response body.
     */
    public function executeSingle(): string
    {
        return $this->executeRequest($this->buildPayloadSingle());
    }

    /**
     * Execute request for multiple recipients.
     *
     * @throws RuntimeException When the API responds with an error or invalid JSON.
     *
     * @return string The successful API response body.
     */
    public function executeMultiple(): string
    {
        return $this->executeRequest($this->buildMultiplePayload());
    }

    /**
     * Build payload for a single recipient.
     *
     * @return array<string,mixed>
     */
    public function buildPayloadSingle(): array
    {
        $recipient = RecipientData::from($this->client);
        $payload = [];

        $this->addIfSet($payload, 'template', $this->client->getTemplate());
        $this->addIfSet($payload, 'firstName', $recipient->firstName ?? $this->client->getFirstName());
        $this->addIfSet($payload, 'lastName', $recipient->lastName ?? $this->client->getLastName());
        $this->addIfSet($payload, 'email', $recipient->email);
        $this->addIfSet($payload, 'phone', $recipient->phone);
        $this->addIfSet($payload, 'scenario', $this->client->getScenario());
        $this->addIfSet($payload, 'account_id', $this->client->getAccountId());

        if ($this->client->getCreatedAt() !== null) {
            $payload['created_at'] = $this->client->getCreatedAt()->format(\DateTimeInterface::ATOM);
        }

        if (!empty($this->client->getOptions())) {
            $payload['options'] = $this->client->getOptions();
        }

        if (!empty($this->client->getFiles())) {
            $payload['files'] = array_map(fn(File $f) => $f->toArray(), $this->client->getFiles());
        }

        if (!empty($this->client->getRemove())) {
            $payload['remove'] = $this->client->getRemove();
        }

        if (!empty($this->client->getExists())) {
            $payload['exists'] = $this->client->getExists();
        }

        return $payload;
    }

    /**
     * Build payload for multiple recipients.
     *
     * @return array<int,array<string,mixed>>
     */
    public function buildMultiplePayload(): array
    {
        $emails = is_array($this->client->getEmail()) ? $this->client->getEmail() : [$this->client->getEmail()];
        $phones = is_array($this->client->getPhone()) ? $this->client->getPhone() : [$this->client->getPhone()];
        $firstNames = is_array($this->client->getFirstName()) ? $this->client->getFirstName() : [$this->client->getFirstName()];
        $lastNames = is_array($this->client->getLastName()) ? $this->client->getLastName() : [$this->client->getLastName()];
        $multiOptions = $this->client->getMultiOptions();
        
        $maxCount = max(count($emails), count($phones), count($firstNames), count($lastNames));
        
        $payloads = [];
        for ($i = 0; $i < $maxCount; $i++) {
            $recipient = RecipientData::from([
                'email' => $emails[$i] ?? null,
                'phone' => $phones[$i] ?? null,
                'firstName' => $firstNames[$i] ?? null,
                'lastName' => $lastNames[$i] ?? null,
            ]);
            
            $options = !empty($multiOptions) ? ($multiOptions[$i] ?? null) : null;
            
            $payloads[] = $this->buildMultipleItemPayload($recipient, $options);
        }

        return $payloads;
    }

    /**
     * Build payload for a single item in a multiple send.
     *
     * @param array<string,mixed>|null $recipientOptions
     * @return array<string,mixed>
     */
    private function buildMultipleItemPayload(
        RecipientData $recipient,
        ?array $recipientOptions
    ): array
    {
        $payload = [];

        $this->addIfSet($payload, 'template', $this->client->getTemplate());
        $this->addIfSet($payload, 'firstName', $recipient->firstName);
        $this->addIfSet($payload, 'lastName', $recipient->lastName);
        $this->addIfSet($payload, 'email', $recipient->email);
        $this->addIfSet($payload, 'phone', $recipient->phone);
        $this->addIfSet($payload, 'scenario', $this->client->getScenario());
        $this->addIfSet($payload, 'account_id', $this->client->getAccountId());

        if ($this->client->getCreatedAt() !== null) {
            $payload['created_at'] = $this->client->getCreatedAt()->format(\DateTimeInterface::ATOM);
        }

        if ($recipientOptions !== null && !empty($recipientOptions)) {
            $payload['options'] = $recipientOptions;
        } elseif (!empty($this->client->getOptions())) {
            $payload['options'] = $this->client->getOptions();
        }

        if (!empty($this->client->getFiles())) {
            $payload['files'] = array_map(fn(File $f) => $f->toArray(), $this->client->getFiles());
        }

        if (!empty($this->client->getRemove())) {
            $payload['remove'] = $this->client->getRemove();
        }

        if (!empty($this->client->getExists())) {
            $payload['exists'] = $this->client->getExists();
        }

        return $payload;
    }

    /**
     * Add a value to the payload if it is set.
     */
    private function addIfSet(array &$payload, string $key, mixed $value): void
    {
        if ($value !== null) {
            $payload[$key] = $value;
        }
    }

    /**
     * Execute the HTTP request.
     *
     * @param array<string,mixed>|array<int,array<string,mixed>> $payload
     * @throws RuntimeException When the API responds with an error or invalid JSON.
     *
     * @return string The successful API response body.
     */
    private function executeRequest(
        array $payload
    ): string
    {
        $url = $this->client->getEndpoint() . $this->path;

        $headers = [
            'Authorization: Bearer ' . $this->client->getApiKey(),
            'Content-Type: application/json',
        ];

        $body = json_encode($payload, JSON_THROW_ON_ERROR);

        $response = $this->client->getHttpClient()->request($this->method, $url, $headers, $body);

        if (!$response->isSuccessful()) {
            throw new RuntimeException(
                'API error, code ' . $response->getStatusCode() . ': ' . $response->getBody()
            );
        }

        return $response->getBody();
    }
}