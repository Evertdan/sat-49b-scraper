<?php

declare(strict_types=1);

namespace PhpCfdi\Sat49BScraper;

use DateTimeImmutable;
use GuzzleHttp\ClientInterface;
use PhpCfdi\Sat49BScraper\Services\DocumentService;
use PhpCfdi\Sat49BScraper\Services\IndexService;

readonly class Scraper implements ScraperInterface
{
    public function __construct(
        public ClientInterface $client,
        public IndexService $indexService,
        public DocumentService $documentService,
    ) {
    }

    public static function create(?ClientInterface $client = null): self
    {
        $client ??= HttpClientFactory::create();
        return new self($client, new IndexService($client), new DocumentService($client));
    }

    /** @return Company[] */
    public function scrape(DateTimeImmutable $date, string $edicion = 'MAT'): array
    {
        $companies = [];
        foreach ($this->indexService->fetchCodes($date, $edicion) as $codigo) {
            array_push($companies, ...$this->documentService->fetchCompanies($codigo, $date));
        }

        return $companies;
    }
}
