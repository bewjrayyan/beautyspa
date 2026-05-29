<?php

namespace Modules\Report\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportArrayExport implements FromArray, WithHeadings
{
    public function __construct(
        private readonly array $headings,
        private readonly array $rows,
    ) {
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
