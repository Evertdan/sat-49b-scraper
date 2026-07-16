<?php

declare(strict_types=1);

namespace PhpCfdi\Sat49BScraper\Tests\Unit;

use DateTimeImmutable;
use PhpCfdi\Sat49BScraper\Company;
use PHPUnit\Framework\TestCase;

class CompanyTest extends TestCase
{
    public function testJsonSerializeReturnsExpectedShape(): void
    {
        $company = new Company('AAA010101AAA', 'Empresa de Prueba SA de CV', new DateTimeImmutable('2026-07-15'));

        $this->assertSame([
            'rfc' => 'AAA010101AAA',
            'name' => 'Empresa de Prueba SA de CV',
            'published_at' => '2026-07-15',
        ], $company->jsonSerialize());
    }
}
