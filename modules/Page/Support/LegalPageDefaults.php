<?php

namespace Modules\Page\Support;

class LegalPageDefaults
{
    public static function body(string $document, string $locale): string
    {
        $data = config("imma_{$document}.{$locale}", []);
        $html = '<p>' . e($data['intro'] ?? '') . '</p>';

        foreach ($data['sections'] ?? [] as $section) {
            $html .= '<h2>' . e($section['title'] ?? '') . '</h2>' . ($section['content'] ?? '');
        }

        if ($document === 'terms') {
            $html .= static::clinicTerms($locale);
        }

        return str_replace(
            ['/contact', '/privacy-policy', '/terms-conditions'],
            [
                localized_url($locale, 'contact'),
                localized_url($locale, 'privacy-policy'),
                localized_url($locale, 'terms-conditions'),
            ],
            $html
        );
    }

    private static function clinicTerms(string $locale): string
    {
        if ($locale === 'ms') {
            return <<<'HTML'
<h2>13. Polisi Klinik, Pembayaran & Pemulangan Wang</h2>
<ul>
<li>Bayaran penuh diperlukan mengikut jumlah yang dipersetujui. Syarikat hanya menerima resit rasmi yang dibayar ke akaun syarikat.</li>
<li>Bayaran tidak akan dipulangkan kecuali atas alasan munasabah seperti kehamilan atau penyakit kronik yang disahkan oleh doktor.</li>
<li>Pelanggan dan beautician berkongsi tanggungjawab untuk memastikan prosedur, penjagaan sebelum dan selepas rawatan dipatuhi dengan selamat.</li>
</ul>
<h2>14. Persediaan Sebelum Rawatan</h2>
<p>Elakkan durian, alkohol, dadah dan tapai sekurang-kurangnya 24 jam sebelum rawatan. Pelanggan disarankan mengambil makanan berat dan minum air secukupnya sebelum hadir.</p>
<h2>15. Waranti Rawatan & Pakej Promosi</h2>
<ul>
<li>Waranti, susulan dan pembetulan tertakluk pada jenis rawatan, tempoh yang dinyatakan, kerjasama pelanggan, penilaian beautician serta kos ubat atau bahan yang berkaitan.</li>
<li>Pakej promosi sah selama 90 hari melainkan dinyatakan sebaliknya dan akan luput selepas tempoh tersebut.</li>
<li>Perkongsian pakej hanya dibenarkan dengan persetujuan pemilik pakej dan pengesahan pihak klinik.</li>
</ul>
<h2>16. Kehadiran, Keselamatan & Persetujuan</h2>
<ul>
<li>Perkhidmatan adalah melalui janji temu. Pelanggan lewat 15 minit mungkin perlu menunggu pelanggan seterusnya; kelewatan 30 minit tanpa notis boleh dibatalkan.</li>
<li>Kanak-kanak bawah 7 tahun tidak digalakkan dibawa kecuali diawasi oleh penjaga lain.</li>
<li>Reaksi dan hasil rawatan berbeza mengikut individu. Semua maklumat konsultasi mestilah benar dan lengkap.</li>
<li>Maklumat pelanggan dirahsiakan. Gambar atau testimoni hanya digunakan dengan persetujuan pelanggan.</li>
</ul>
HTML;
        }

        return <<<'HTML'
<h2>13. Clinic, Payment & Refund Policy</h2>
<ul>
<li>Full payment is required for the agreed amount. The company accepts only official receipts paid to the company account.</li>
<li>Payments are non-refundable except for reasonable circumstances such as pregnancy or chronic illness confirmed by a doctor.</li>
<li>The customer and beautician share responsibility for following treatment, pre-care and aftercare instructions safely.</li>
</ul>
<h2>14. Pre-treatment Preparation</h2>
<p>Avoid durian, alcohol, recreational drugs and tapai for at least 24 hours before treatment. Customers should eat a substantial meal and drink sufficient water before attending.</p>
<h2>15. Treatment Warranty & Promotional Packages</h2>
<ul>
<li>Warranty, follow-up and corrective services depend on the treatment, stated period, customer cooperation, beautician assessment, and any related medicine or material charges.</li>
<li>Promotional packages are valid for 90 days unless otherwise stated and expire after that period.</li>
<li>A package may be shared only with the package owner's consent and clinic confirmation.</li>
</ul>
<h2>16. Attendance, Safety & Consent</h2>
<ul>
<li>Services are by appointment. A customer who is 15 minutes late may need to wait for the next customer; an appointment may be cancelled after 30 minutes without notice.</li>
<li>Children under 7 are discouraged unless supervised by another guardian.</li>
<li>Treatment reactions and outcomes vary. All consultation information must be truthful and complete.</li>
<li>Customer information is confidential. Images or testimonials are used only with customer consent.</li>
</ul>
HTML;
    }
}
