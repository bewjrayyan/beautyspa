<?php

namespace Modules\Account\Services;

use Dompdf\Dompdf;
use Modules\Account\Contracts\PdfRenderer;
use Modules\Support\Services\DompdfConfigurator;

class DompdfPdfRenderer implements PdfRenderer
{
    public function render(string $html): string
    {
        $dompdf = new Dompdf(DompdfConfigurator::createOptions(true));
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
