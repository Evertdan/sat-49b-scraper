<?php

declare(strict_types=1);

namespace PhpCfdi\Sat49BScraper;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;

class HttpClientFactory
{
    /** @phpstan-param array $customOptions */
    public function __construct(private array $customOptions = [])
    {
    }

    /** @phpstan-param array $options */
    public static function create(array $options = []): ClientInterface
    {
        $factory = new self($options);
        return $factory->build();
    }

    public function build(): ClientInterface
    {
        return new Client($this->buildOptions());
    }

    /** @phpstan-return array */
    public function buildOptions(): array
    {
        return array_replace($this->getDefaultOptions(), $this->customOptions);
    }

    /**
     * @phpstan-return array
     *
     * VERIFY se desactiva por defecto porque la cadena de certificados de dof.gob.mx
     * ha fallado intermitentemente con curl/openssl en el pasado.
     */
    public static function getDefaultOptions(): array
    {
        return [
            'base_uri' => Url::$base,
            RequestOptions::TIMEOUT => 30,
            RequestOptions::VERIFY => false,
            RequestOptions::HEADERS => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 '
                    . '(KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            ],
        ];
    }
}
