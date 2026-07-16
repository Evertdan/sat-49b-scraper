<?php

declare(strict_types=1);

namespace PhpCfdi\Sat49BScraper;

use DateTimeImmutable;
use JsonSerializable;

readonly class Company implements JsonSerializable
{
    public const string RFC_PATTERN = '/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/u';

    public function __construct(
        public string $rfc,
        public string $name,
        public DateTimeImmutable $publishedAt,
    ) {
    }

    /** @return array{rfc: string, name: string, published_at: string} */
    public function jsonSerialize(): array
    {
        return [
            'rfc' => $this->rfc,
            'name' => $this->name,
            'published_at' => $this->publishedAt->format('Y-m-d'),
        ];
    }
}
