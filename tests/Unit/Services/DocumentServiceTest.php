<?php

declare(strict_types=1);

namespace PhpCfdi\Sat49BScraper\Tests\Unit\Services;

use DateTimeImmutable;
use GuzzleHttp\Client;
use PhpCfdi\Sat49BScraper\Services\DocumentService;
use PHPUnit\Framework\TestCase;

class DocumentServiceTest extends TestCase
{
    public function testExtractCompaniesFiltersRowsByRfcPattern(): void
    {
        $html = <<<'HTML'
            <html><body>
                <div id="DivDetalleNota">
                    <table>
                        <tr><td>AAA010101AAA</td><td>Empresa Valida SA de CV</td></tr>
                        <tr><td>no es un rfc</td><td>Fila invalida</td></tr>
                        <tr><td>encabezado</td><td>Nombre</td></tr>
                    </table>
                </div>
            </body></html>
            HTML;

        $service = new DocumentService(new Client());
        $companies = $service->extractCompanies($html, new DateTimeImmutable('2026-07-15'));

        $this->assertCount(1, $companies);
        $this->assertSame('AAA010101AAA', $companies[0]->rfc);
        $this->assertSame('Empresa Valida SA de CV', $companies[0]->name);
    }

    public function testExtractCompaniesReturnsEmptyArrayWhenDivMissing(): void
    {
        $service = new DocumentService(new Client());
        $this->assertSame([], $service->extractCompanies('<html><body>sin anexo</body></html>', new DateTimeImmutable()));
    }
}
