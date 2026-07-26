<?php

namespace Modules\Account\Contracts;

interface PdfRenderer
{
    public function render(string $html): string;
}
