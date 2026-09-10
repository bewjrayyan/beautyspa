<?php

namespace Tests\Feature;

use Modules\Lead\Services\LeadImportService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LeadImportServiceTest extends TestCase
{
    #[Test]
    public function it_parses_pipe_and_csv_paste_lines(): void
    {
        $service = app(LeadImportService::class);

        $rows = $service->parsePaste(
            "Name | Phone | Email\n".
            "Ina | +60176288341 | ina@example.com\n".
            "Sarah,0123334567,sarah@example.com\n".
            "BadRowOnlyName\n"
        );

        $this->assertCount(2, $rows);
        $this->assertSame('Ina', $rows[0]['name']);
        $this->assertSame('+60176288341', $rows[0]['phone']);
        $this->assertSame('ina@example.com', $rows[0]['email']);
        $this->assertSame('Sarah', $rows[1]['name']);
        $this->assertSame('0123334567', $rows[1]['phone']);
    }

    #[Test]
    public function preview_marks_invalid_and_ready_rows(): void
    {
        $service = app(LeadImportService::class);

        $preview = $service->preview([
            'method' => 'paste',
            'paste' => "Ready Lead | 01999888771 | ready@example.com\nInvalid | - | bad@email",
        ]);

        $this->assertSame(2, $preview['summary']['total']);
        $this->assertGreaterThanOrEqual(1, $preview['summary']['ready'] + $preview['summary']['existing'] + $preview['summary']['duplicate']);
        $this->assertGreaterThanOrEqual(1, $preview['summary']['invalid']);
        $this->assertContains($preview['rows'][0]['detection'], ['READY', 'EXISTING', 'DUPLICATE']);
        $this->assertSame('INVALID', $preview['rows'][1]['detection']);
    }
}
