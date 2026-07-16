<?php

declare(strict_types=1);

namespace PhpCfdi\Sat49BScraper;

use DateTimeImmutable;

interface ScraperInterface
{
    /** @return Company[] */
    public function scrape(DateTimeImmutable $date, string $edicion = 'MAT'): array;
}
