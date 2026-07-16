<?php

declare(strict_types=1);

namespace PhpCfdi\Sat49BScraper\Services;

use DateTimeImmutable;
use GuzzleHttp\ClientInterface;
use PhpCfdi\Sat49BScraper\Company;
use PhpCfdi\Sat49BScraper\Url;
use Symfony\Component\DomCrawler\Crawler;

/** Extrae las empresas listadas en el anexo de una nota_detalle del DOF. */
readonly class DocumentService
{
    use FetchesDof;

    public function __construct(public ClientInterface $client)
    {
    }

    /** @return Company[] */
    public function fetchCompanies(string $codigo, DateTimeImmutable $publishedAt): array
    {
        $html = $this->fetchBody(Url::$noteDetail, [
            'codigo' => $codigo,
            'fecha' => $publishedAt->format('d/m/Y'),
        ]);

        return $this->extractCompanies($html, $publishedAt);
    }

    /** @return Company[] */
    public function extractCompanies(string $html, DateTimeImmutable $publishedAt): array
    {
        $crawler = new Crawler(str_ireplace('<br>', ' ', $html));
        $div = $crawler->filter('#DivDetalleNota');
        if ($div->count() === 0) {
            return [];
        }

        $companies = [];
        $div->filter('table tr')->each(function (Crawler $row) use (&$companies, $publishedAt): void {
            $cells = $row->filter('td')->each(
                static fn (Crawler $td): string => trim(preg_replace('/\s+/', ' ', $td->text()) ?? ''),
            );

            $rfc = $cells[0] ?? '';
            if (preg_match(Company::RFC_PATTERN, $rfc) === 1) {
                $companies[] = new Company($rfc, $cells[1] ?? '', $publishedAt);
            }
        });

        return $companies;
    }
}
