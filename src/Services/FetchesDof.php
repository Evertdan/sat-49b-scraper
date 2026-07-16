<?php

declare(strict_types=1);

namespace PhpCfdi\Sat49BScraper\Services;

use PhpCfdi\Sat49BScraper\Exceptions\NetworkException;
use Psr\Http\Client\ClientExceptionInterface;

trait FetchesDof
{
    /** @phpstan-param array<string, string> $query */
    private function fetchBody(string $url, array $query): string
    {
        try {
            $response = $this->client->request('GET', $url, ['query' => $query]);
            return (string) $response->getBody();
        } catch (ClientExceptionInterface $e) {
            throw new NetworkException("Fallo al consultar {$url}", $e);
        }
    }
}
