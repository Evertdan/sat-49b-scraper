<?php

declare(strict_types=1);

namespace PhpCfdi\Sat49BScraper\Tests\Unit;

use DateTimeImmutable;
use GuzzleHttp\Client;
use PhpCfdi\Sat49BScraper\Services\DocumentService;
use PhpCfdi\Sat49BScraper\Services\IndexService;
use PHPUnit\Framework\TestCase;

/**
 * PHP side of the PHP↔Java parity guard (Story 3.2), trans-repo.
 *
 * Offline dump of the versioned DOF fixtures to the canonical parity schema
 * (array of {rfc, name, published_at}, published_at in Y-m-d), mirror of the Java
 * com.ln69.integration.ParityDumpTest. Additive test/tooling only — the src/ oracle
 * package (D4: frozen) is never touched; extractCodes/extractCompanies are already public.
 *
 * Enumerates every index_<yyyyMMdd>_<ED>.html in PARITY_FIXTURES_DIR (default tests/_files/),
 * derives (fecha, edicion) from the file name, extracts codes and companies with zero network,
 * sorts stably by rfc then published_at, and writes build/parity/php-dump.json.
 */
class ParityDumpTest extends TestCase
{
    public function testDumpsCanonicalParityJsonWithTheThreeKnownRfcs(): void
    {
        $fixturesDir = rtrim(getenv('PARITY_FIXTURES_DIR') ?: __DIR__ . '/../_files', '/');
        $this->assertDirectoryExists($fixturesDir, "fixtures dir not found: {$fixturesDir}");

        $indexService = new IndexService(new Client());
        $documentService = new DocumentService(new Client());

        $indexFiles = glob($fixturesDir . '/index_*.html') ?: [];
        sort($indexFiles);
        $this->assertNotEmpty($indexFiles, "no index_*.html fixtures in {$fixturesDir}");

        $rows = [];
        foreach ($indexFiles as $indexPath) {
            if (preg_match('/^index_(\d{8})_([A-Z]+)\.html$/', basename($indexPath), $m) !== 1) {
                continue;
            }
            $yyyymmdd = $m[1];
            $iso = substr($yyyymmdd, 0, 4) . '-' . substr($yyyymmdd, 4, 2) . '-' . substr($yyyymmdd, 6, 2);
            $publishedAt = new DateTimeImmutable($iso);

            $indexHtml = (string) file_get_contents($indexPath);
            foreach ($indexService->extractCodes($indexHtml) as $code) {
                $notaPath = $fixturesDir . '/nota_' . $code . '_' . $yyyymmdd . '.html';
                $this->assertFileExists($notaPath, "missing nota fixture: {$notaPath}");
                $notaHtml = (string) file_get_contents($notaPath);
                foreach ($documentService->extractCompanies($notaHtml, $publishedAt) as $company) {
                    // {rfc, name, published_at(Y-m-d)} — the canonical schema, straight from the oracle.
                    $rows[] = $company->jsonSerialize();
                }
            }
        }

        // Stable order: by rfc, then published_at — identical to the Java dump's sort.
        usort($rows, static fn (array $a, array $b): int
            => [$a['rfc'], $a['published_at']] <=> [$b['rfc'], $b['published_at']]);

        $json = json_encode(
            $rows,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );
        $this->assertIsString($json, 'json_encode of the parity rows must succeed');
        self::assertNotFalse($json, 'json_encode failed: ' . json_last_error_msg());

        $outDir = dirname(__DIR__, 2) . '/build/parity';
        if (! is_dir($outDir)) {
            self::assertTrue(mkdir($outDir, 0o777, true) || is_dir($outDir), "could not create dir: {$outDir}");
        }
        $outFile = $outDir . '/php-dump.json';
        self::assertNotFalse(file_put_contents($outFile, $json . "\n"), "could not write dump: {$outFile}");

        $rfcs = array_column($rows, 'rfc');
        $this->assertContains('ACC210823UA5', $rfcs);
        $this->assertContains('APR181217P21', $rfcs);
        $this->assertContains('CSP170608IV8', $rfcs);
        $this->assertCount(3, $rows, 'the current fixtures must yield exactly 3 records');
    }
}
