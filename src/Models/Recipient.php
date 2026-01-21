<?php

declare(strict_types=1);

namespace KandMailer\Models;

use InvalidArgumentException;

class Recipient
{
    /**
     * Create a new Recipient instance.
     *
     * @param string|null $email Email address
     * @param string|null $phone Phone number
     * @param string|null $firstName First name
     * @param string|null $lastName Last name
     * @param array<string,mixed> $options Custom options for this recipient
     * @param string|null $scenario Scenario identifier
     * @param string|null $accountId Account identifier
     * @param \DateTimeInterface|null $createdAt Creation date
     *
     * @throws InvalidArgumentException If email or phone format is invalid
     */
    public function __construct(
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
        public readonly ?string $firstName = null,
        public readonly ?string $lastName = null,
        public readonly array $options = [],
        public readonly ?string $scenario = null,
        public readonly ?string $accountId = null,
        public readonly ?\DateTimeInterface $createdAt = null,
    ) {
        $this->validate();
    }

    /**
     * Validate recipient data.
     *
     * @throws InvalidArgumentException If validation fails
     */
    private function validate(): void
    {
        if ($this->email === null && $this->phone === null) {
            throw new InvalidArgumentException(
                'Au moins un email ou un téléphone doit être fourni.'
            );
        }

        if ($this->email !== null) {
            if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException("Email invalide: {$this->email}");
            }
        }

        if ($this->phone !== null) {
            $digits = preg_replace('/\D/', '', $this->phone);
            if (strlen($digits) < 8) {
                throw new InvalidArgumentException("Numéro de téléphone invalide: {$this->phone}");
            }
        }
    }

    /**
     * Create a Recipient from an array.
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            firstName: $data['firstName'] ?? null,
            lastName: $data['lastName'] ?? null,
            options: $data['options'] ?? [],
            scenario: $data['scenario'] ?? null,
            accountId: $data['accountId'] ?? null,
            createdAt: $data['createdAt'] ?? null,
        );
    }

    /**
     * Convert recipient to array format.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->email !== null) {
            $data['email'] = $this->email;
        }

        if ($this->phone !== null) {
            $data['phone'] = $this->phone;
        }

        if ($this->firstName !== null) {
            $data['firstName'] = $this->firstName;
        }

        if ($this->lastName !== null) {
            $data['lastName'] = $this->lastName;
        }

        if (!empty($this->options)) {
            $data['options'] = $this->options;
        }

        if ($this->scenario !== null) {
            $data['scenario'] = $this->scenario;
        }

        if ($this->accountId !== null) {
            $data['accountId'] = $this->accountId;
        }

        if ($this->createdAt !== null) {
            $data['createdAt'] = $this->createdAt;
        }

        return $data;
    }
}
