<?php

declare(strict_types=1);

namespace KandMailer\Helpers;

use RuntimeException;

use KandMailer\MailerClient;
use KandMailer\Models\File;
use KandMailer\Models\RecipientData;

class Makers
{
    /**
     * Send a message.
     *
     * @throws RuntimeException When the API responds with an error or invalid JSON.
     *
     * @return string The successful API response body.
     */
    public static function request(
        MailerClient $client,
        string $method,
        string $path
    ): string
    {
        $url = $client->getEndpoint() . $path;

        $headers = [
            'Authorization: Bearer ' . $client->getApiKey(),
            'Content-Type: application/json',
        ];

        $body = json_encode(self::toPayload($client, str_contains($path, '/send/list')), JSON_THROW_ON_ERROR);

        $response = $client->getHttpClient()->request($method, $url, $headers, $body);

        if (!$response->isSuccessful()) {
            throw new RuntimeException(
                'API error, code ' . $response->getStatusCode() . ': ' . $response->getBody()
            );
        }

        return $response->getBody();
    }

    /**
     * Build the payload(s) for a request.
     *
     * @param bool $multiple If true, returns an array of payloads for multiple recipients
     * @return array<string,mixed>|array<int,array<string,mixed>>
     */
    public static function toPayload(MailerClient $client, bool $isMultiple = false): array
    {
        // For multiple recipients, create an array of payloads
        if ($isMultiple) {
            // Convert all fields to arrays and determine the max length
            $emails = is_array($client->getEmail()) ? $client->getEmail() : [$client->getEmail()];
            $phones = is_array($client->getPhone()) ? $client->getPhone() : [$client->getPhone()];
            $firstNames = is_array($client->getFirstName()) ? $client->getFirstName() : [$client->getFirstName()];
            $lastNames = is_array($client->getLastName()) ? $client->getLastName() : [$client->getLastName()];
            
            // Determine the maximum count to iterate
            $maxCount = max(count($emails), count($phones), count($firstNames), count($lastNames));
            
            // Build recipients by "zipping" arrays together
            $recipients = [];
            for ($i = 0; $i < $maxCount; $i++) {
                $recipients[] = [
                    'email' => $emails[$i] ?? null,
                    'phone' => $phones[$i] ?? null,
                    'firstName' => $firstNames[$i] ?? null,
                    'lastName' => $lastNames[$i] ?? null,
                ];
            }

            $payloads = [];
            foreach ($recipients as $recipient) {
                $payloads[] = self::buildSinglePayload(
                    $client, 
                    RecipientData::from($recipient),
                    true  // Use provided values only, no fallback
                );
            }

            return $payloads;
        }

        // For single recipient, return a single payload
        return self::buildSinglePayload(
            $client, 
            RecipientData::from($client)
        );
    }

    /**
     * Build a single payload.
     *
     * @param bool $useProvidedValues If true, only use provided values without fallback to client
     * @return array<string,mixed>
     */
    private static function buildSinglePayload(
        MailerClient $client, 
        RecipientData $recipient,
        bool $useProvidedValues = false
    ): array
    {
        $payload = [];

        // Simple fields
        self::addIfSet($payload, 'template', $client->getTemplate());
        
        // For multiple recipients, use only provided values (no fallback)
        if ($useProvidedValues) {
            self::addIfSet($payload, 'firstName', $recipient->firstName);
            self::addIfSet($payload, 'lastName', $recipient->lastName);
        } else {
            // For single recipient, fallback to client values if not provided
            self::addIfSet($payload, 'firstName', $recipient->firstName ?? $client->getFirstName());
            self::addIfSet($payload, 'lastName', $recipient->lastName ?? $client->getLastName());
        }
        
        self::addIfSet($payload, 'email', $recipient->email);
        self::addIfSet($payload, 'phone', $recipient->phone);
        self::addIfSet($payload, 'scenario', $client->getScenario());
        self::addIfSet($payload, 'account_id', $client->getAccountId());

        // Date
        if ($client->getCreatedAt() !== null) {
            $payload['created_at'] = $client->getCreatedAt()->format(\DateTimeInterface::ATOM);
        }

        // Options (only if not empty)
        if (!empty($client->getOptions())) {
            $payload['options'] = $client->getOptions();
        }

        // Files (only if not empty)
        if (!empty($client->getFiles())) {
            $payload['files'] = array_map(fn(File $f) => $f->toArray(), $client->getFiles());
        }

        // Arrays (only if not empty)
        if (!empty($client->getRemove())) {
            $payload['remove'] = $client->getRemove();
        }

        if (!empty($client->getExists())) {
            $payload['exists'] = $client->getExists();
        }

        return $payload;
    }

    /**
     * Add a value to the payload if it is set.
     */
    public static function addIfSet(array &$payload, string $key, mixed $value): void
    {
        if ($value !== null) {
            $payload[$key] = $value;
        }
    }
}