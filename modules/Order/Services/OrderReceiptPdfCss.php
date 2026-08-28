<?php

namespace Modules\Order\Services;

use Illuminate\Support\Facades\File;

class OrderReceiptPdfCss
{
    public function inline(): string
    {
        $css = $this->compiledReceiptCss();
        $primary = function_exists('storefront_theme_color') ? storefront_theme_color() : '#0068e1';

        $css = preg_replace('/var\(--color-primary(?:,\s*[^)]+)?\)/', $primary, $css) ?? $css;

        return $css.$this->dompdfOverrides($primary);
    }


    private function compiledReceiptCss(): string
    {
        $manifestPath = public_path('build/manifest.json');

        if (! File::exists($manifestPath)) {
            return '';
        }

        $manifest = json_decode(File::get($manifestPath), true);

        if (! is_array($manifest)) {
            return '';
        }

        $entry = $manifest['modules/Order/Resources/assets/admin/sass/receipt.scss'] ?? null;
        $relativeFile = is_array($entry) ? ($entry['file'] ?? null) : null;

        if (! is_string($relativeFile) || $relativeFile === '') {
            return '';
        }

        $cssPath = public_path('build/'.ltrim($relativeFile, '/'));

        if (! File::exists($cssPath)) {
            return '';
        }

        return (string) File::get($cssPath);
    }


    private function dompdfOverrides(string $primary): string
    {
        return <<<CSS

/* Dompdf overrides — 80mm thermal receipt */
@page {
    size: 80mm 297mm;
    margin: 3mm;
}

body {
    background: #fff !important;
    font-family: helvetica, sans-serif !important;
    margin: 0 !important;
}

.order-receipt {
    max-width: 100% !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 4px 2px !important;
    background: #fff !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    font-size: 11px !important;
}

.order-receipt__store {
    font-size: 14px !important;
}

.order-receipt__title {
    font-size: 12px !important;
    padding: 8px 0 !important;
}

.order-receipt__items {
    font-size: 10px !important;
}

.order-receipt__title,
.order-receipt__total-row--grand dd {
    color: {$primary} !important;
}

.order-receipt__meta-row,
.order-receipt__total-row {
    display: block !important;
    width: 100% !important;
    overflow: hidden !important;
}

.order-receipt__meta-row dt,
.order-receipt__meta-row dd,
.order-receipt__total-row dt,
.order-receipt__total-row dd {
    display: inline-block !important;
    width: 48% !important;
    vertical-align: top !important;
}

.order-receipt__meta-row dd,
.order-receipt__total-row dd {
    width: 50% !important;
    text-align: right !important;
}

.order-receipt-toolbar,
.order-receipt-flash,
.order-receipt-wrap {
    display: none !important;
}

CSS;
    }
}
