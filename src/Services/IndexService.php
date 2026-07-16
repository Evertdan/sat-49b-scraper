<?php

declare(strict_types=1);

namespace PhpCfdi\Sat49BScraper\Services;

use DateTimeImmutable;
use GuzzleHttp\ClientInterface;
use PhpCfdi\Sat49BScraper\Url;
use Symfony\Component\DomCrawler\Crawler;

/** Localiza en el índice diario del DOF los códigos de nota que mencionan "49 Bis". */
readonly class IndexService
{
    use FetchesDof;

    public function __construct(public ClientInterface $client)
    {
    }

    /** @return string[] */
    public function fetchCodes(DateTimeImmutable $date, string $edicion = 'MAT'): array
    {
        $html = $this->fetchBody(Url::$index, [
            'year' => $date->format('Y'),
            'month' => $date->format('m'),
            'day' => $date->format('d'),
            'edicion' => $edicion,
        ]);

        return $this->extractCodes($html);
    }

    /** @return string[] */
    public function extractCodes(string $html): array
    {
        $codes = [];
        new Crawler($html)->filter('a[href*="nota_detalle.php"]')->each(function (Crawler $link) use (&$codes): void {
            $text = trim(preg_replace('/\s+/', ' ', $link->text()) ?? '');
            if (stripos($text, '49 Bis') === false) {
                return;
            }

            $href = $link->attr('href') ?? '';
            if (preg_match('/codigo=(\d+)/', $href, $m) === 1) {
                $codes[$m[1]] = $m[1];
            }
        });

        return array_values($codes);
    }
}
