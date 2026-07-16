<?php

declare(strict_types=1);

namespace PhpCfdi\Sat49BScraper\Tests\Unit\Services;

use GuzzleHttp\Client;
use PhpCfdi\Sat49BScraper\Services\IndexService;
use PHPUnit\Framework\TestCase;

class IndexServiceTest extends TestCase
{
    public function testExtractCodesReturnsOnlyLinksMentioning49Bis(): void
    {
        $html = <<<'HTML'
            <html><body>
                <a href="nota_detalle.php?codigo=111&fecha=15/07/2026">RESOLUCION Articulo 49 Bis del CFF</a>
                <a href="nota_detalle.php?codigo=222&fecha=15/07/2026">Otra nota sin relacion</a>
                <a href="nota_detalle.php?codigo=333&fecha=15/07/2026">Segunda resolucion 49 Bis</a>
            </body></html>
            HTML;

        $service = new IndexService(new Client());
        $this->assertSame(['111', '333'], $service->extractCodes($html));
    }
}
