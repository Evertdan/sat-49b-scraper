# sat-49b-scraper

Obtiene el listado de empresas publicadas en el DOF bajo el articulo 49-B del CFF
(listas negras/blancas del SAT).

## Instalacion

```bash
composer require phpcfdi/sat-49b-scraper
```

## Uso como libreria

```php
use PhpCfdi\Sat49BScraper\Scraper;

$scraper = Scraper::create();
$companies = $scraper->scrape(new DateTimeImmutable('2026-07-15'), 'MAT');

foreach ($companies as $company) {
    echo $company->rfc, ' ', $company->name, PHP_EOL;
}

echo json_encode($companies, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
```

## Uso por CLI

```bash
vendor/bin/sat-49b-scraper 2026-07-15 MAT
```

## Como funciona

1. `Services\IndexService` busca en `dof.gob.mx/index.php` del dia/edicion dado
   los enlaces a `nota_detalle.php` cuyo texto menciona "49 Bis".
2. `Services\DocumentService` descarga cada nota y extrae RFC/nombre de las
   tablas dentro de `#DivDetalleNota`, filtrando filas cuyo RFC no cumple el
   patron valido.
3. `Scraper` orquesta ambos servicios y regresa un arreglo de `Company`.
