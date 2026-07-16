# phpcfdi/sat-49b-scraper

[![Source Code][badge-source]][source]
[![PHP Version][badge-php-version]][php-version]
[![Latest Version][badge-release]][release]
[![Software License][badge-license]][license]
[![Total Downloads][badge-downloads]][downloads]

> Scraper para obtener el listado de empresas del articulo 49-B del CFF publicadas en el DOF

:mexico: La documentacion de este proyecto esta en espanol, ya que este es el idioma natural para la audiencia
a la que va dirigido.

## Acerca de phpcfdi/sat-49b-scraper

Esta herramienta se conecta usando [*Guzzle*](https://docs.guzzlephp.org/) como cliente HTTP al
[Diario Oficial de la Federacion](https://www.dof.gob.mx/) para localizar, en el indice diario de una fecha y
edicion dadas, las notas que mencionan "49 Bis" y extraer del anexo de cada una el listado de empresas
(RFC y nombre) que ahi se publican.

## Instalacion usando composer

```shell
composer require phpcfdi/sat-49b-scraper
```

## Uso Basico

```php
<?php

use DateTimeImmutable;
use PhpCfdi\Sat49BScraper\Scraper;

$scraper = Scraper::create();

$companies = $scraper->scrape(new DateTimeImmutable('2026-07-15'), 'MAT');

foreach ($companies as $company) {
    echo $company->rfc, ' ', $company->name, PHP_EOL;
}

echo json_encode($companies, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
```

`Scraper::scrape()` regresa un arreglo de objetos `Company` (`rfc`, `name`, `publishedAt`), cada uno
`JsonSerializable` con el formato `{"rfc", "name", "published_at"}`.

Si necesitas configurar el cliente HTTP (proxy, timeouts, etc.) puedes construirlo tu mismo con
`HttpClientFactory` o pasar tu propio `GuzzleHttp\ClientInterface` a `Scraper::create()`:

```php
use GuzzleHttp\RequestOptions;
use PhpCfdi\Sat49BScraper\HttpClientFactory;
use PhpCfdi\Sat49BScraper\Scraper;

$client = HttpClientFactory::create([
    RequestOptions::TIMEOUT => 60,
]);

$scraper = Scraper::create($client);
```

### Uso por linea de comandos

```shell
vendor/bin/sat-49b-scraper 2026-07-15 MAT
```

Imprime el mismo listado de empresas como JSON. El primer argumento es la fecha (cualquier formato
aceptado por `DateTimeImmutable`, por defecto `today`) y el segundo la edicion del DOF (`MAT` o `VES`,
por defecto `MAT`).

## Como funciona

1. `Services\IndexService` descarga `dof.gob.mx/index.php` para el dia y edicion dados y localiza los
   enlaces a `nota_detalle.php` cuyo texto menciona "49 Bis".
2. `Services\DocumentService` descarga cada nota y extrae RFC/nombre de las tablas dentro de
   `#DivDetalleNota`, descartando filas cuyo RFC no cumple el patron valido.
3. `Scraper` orquesta ambos servicios y regresa un arreglo de `Company`.

## Soporte

Puedes obtener soporte abriendo un ticket en Github.

## Compatibilidad

Esta libreria se mantendra compatible con al menos la version con
[soporte activo de PHP](https://www.php.net/supported-versions.php) mas reciente.

| Version | PHP Minima | Nota       |
|---------|------------|------------|
| 0.1.0   | 8.4        | 2026-07-16 |

## Copyright and License

Autor original: Cesar Aguilera `cesargnu29@gmail.com`.

The `phpcfdi/sat-49b-scraper` tool is copyright © [PhpCfdi](https://www.phpcfdi.com/)
and licensed for use under the MIT License (MIT). Please see [LICENSE][] for more information.

[license]: https://github.com/phpcfdi/sat-49b-scraper/blob/main/LICENSE

[source]: https://github.com/phpcfdi/sat-49b-scraper
[php-version]: https://packagist.org/packages/phpcfdi/sat-49b-scraper
[release]: https://github.com/phpcfdi/sat-49b-scraper/releases
[downloads]: https://packagist.org/packages/phpcfdi/sat-49b-scraper

[badge-source]: https://img.shields.io/badge/source-phpcfdi/sat--49b--scraper-blue?logo=github
[badge-php-version]: https://img.shields.io/packagist/dependency-v/phpcfdi/sat-49b-scraper/php?logo=php
[badge-release]: https://img.shields.io/github/release/phpcfdi/sat-49b-scraper?logo=git
[badge-license]: https://img.shields.io/github/license/phpcfdi/sat-49b-scraper?logo=open-source-initiative
[badge-downloads]: https://img.shields.io/packagist/dt/phpcfdi/sat-49b-scraper?logo=packagist
