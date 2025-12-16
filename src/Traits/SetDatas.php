<?php

declare(strict_types=1);

namespace KandMailer\Traits;

use KandMailer\Models\File;

trait SetDatas
{
    /**
     * Set the template.
     * 
     * @return self
     */
    public function template(string $template): self
    {
        $this->template = $template;
        return $this;
    }

    /**
     * Set the recipient to an email.
     * 
     * @param string|array<string> $emails
     * @return self
     * @throws \InvalidArgumentException If email is invalid
     */
    public function toEmail(string|array $emails): self
    {
        $this->validateEmails($emails);
        $this->email = $emails;
        return $this;
    }

    /**
     * Set the recipient to a phone.
     * 
     * @param string|array<string> $phones
     * @return self
     * @throws \InvalidArgumentException If phone is invalid
     */
    public function toPhone(string|array $phones): self
    {
        $this->validatePhones($phones);
        $this->phone = $phones;
        return $this;
    }

    /**
     * Set the email (alias for toEmail, kept for compatibility).
     * 
     * @return self
     * @throws \InvalidArgumentException If email is invalid
     */
    public function email(string|array $email): self
    {
        return $this->toEmail($email);
    }

    /**
     * Set the phone (alias for toPhone, kept for compatibility).
     * 
     * @return self
     * @throws \InvalidArgumentException If phone is invalid
     */
    public function phone(string|array $phone): self
    {
        return $this->toPhone($phone);
    }

    /**
     * Set the first name.
     * 
     * @return self
     */
    public function firstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * Set the last name.
     * 
     * @return self
     */
    public function lastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * Set the scenario.
     * 
     * @return self
     */
    public function scenario(string $scenario): self
    {
        $this->scenario = $scenario;
        return $this;
    }

    /**
     * Set the account ID.
     * 
     * @return self
     */
    public function accountId(string $accountId): self
    {
        $this->accountId = $accountId;
        return $this;
    }

    /**
     * Set the created at date.
     * 
     * @return self
     */
    public function createdAt(\DateTimeInterface $date): self
    {
        $this->createdAt = $date;
        return $this;
    }

    /**
     * Add an option.
     */
    public function option(string $key, mixed $value): self
    {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * Set all options at once.
     *
     * @param array<string,mixed> $options
     */
    public function options(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Add a file.
     */
    public function file(string $label, string $publicId, string $secretId): self
    {
        $this->files[] = new File($label, $publicId, $secretId);
        return $this;
    }

    /**
     * Add multiple files.
     *
     * @param array<File> $files
     */
    public function files(array $files): self
    {
        $this->files = array_merge($this->files, $files);
        return $this;
    }

    /**
     * Keys to remove.
     *
     * @param array<string> $keys
     */
    public function setRemove(array $keys): self
    {
        $this->remove = $keys;
        return $this;
    }

    /**
     * Keys to check.
     *
     * @param array<string> $keys
     */
    public function setExists(array $keys): self
    {
        $this->exists = $keys;
        return $this;
    }

    /**
     * Reset the client for a new send (keep apiKey and endpoint).
     */
    public function reset(): self
    {
        $this->template = null;
        $this->firstName = null;
        $this->lastName = null;
        $this->email = null;
        $this->phone = null;
        $this->scenario = null;
        $this->accountId = null;
        $this->createdAt = null;
        $this->options = [];
        $this->files = [];
        $this->remove = [];
        $this->exists = [];

        return $this;
    }
}

