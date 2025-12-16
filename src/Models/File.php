<?php

declare(strict_types=1);

namespace KandMailer\Models;

/**
 * Représente un fichier attaché.
 */
class File
{
    public function __construct(
        private string $label,
        private string $publicId,
        private string $secretId,
    ) {}

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getId(): string
    {
        return $this->publicId . '/' . $this->secretId;
    }

    /**
     * @return array{label: string, id: string}
     */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'id'    => $this->getId(),
        ];
    }
}

