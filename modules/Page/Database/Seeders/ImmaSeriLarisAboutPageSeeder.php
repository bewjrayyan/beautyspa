<?php

namespace Modules\Page\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Page\Entities\Page;
use Modules\SpaBranch\Entities\SpaBranch;

class ImmaSeriLarisAboutPageSeeder extends Seeder
{
    /**
     * Update About Us page with modern HTML from immaserilaris.com.
     *
     * @return void
     */
    public function run(): void
    {
        $page = Page::withoutGlobalScope('active')->where('slug', 'about-us')->first();

        if (! $page) {
            $this->command?->error('About Us page (slug: about-us) not found.');

            return;
        }

        $page->update(['is_active' => true]);

        $images = $this->syncAboutImages();
        $contactEn = localized_url('en', 'contact');
        $contactMs = localized_url('ms', 'contact');
        $branchCount = $this->activeBranchCount();

        foreach ([
            'en' => ['name' => 'About Us', 'body' => $this->buildBody($this->englishCopy($contactEn, $branchCount), $images)],
            'ms' => ['name' => 'Tentang Kami', 'body' => $this->buildBody($this->malayCopy($contactMs, $branchCount), $images)],
        ] as $locale => $data) {
            DB::table('page_translations')->updateOrInsert(
                ['page_id' => $page->id, 'locale' => $locale],
                $data
            );
        }

        $page->saveMetaData([
            'meta_title' => 'About Us — IMMA Seri Laris SPA & Aesthetic',
            'meta_description' => 'IMMA Seri Laris — Muslimah-friendly spa, aesthetic & estetik centre in Kuala Lumpur & Sungai Petani since 2014. Aromatherapy, facial, Pico Laser, bridal & mobile spa.',
        ]);

        $this->command?->info('About Us page updated with modern layout for IMMA Seri Laris.');
    }


    /**
     * @return array<string, string>
     */
    private function syncAboutImages(): array
    {
        $dir = public_path('storage/media/imma-about');

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $remote = [
            'hero' => 'https://i0.wp.com/immaserilaris.com/wp-content/uploads/2024/11/immaserilaris-building.webp',
            'founder' => 'https://i0.wp.com/immaserilaris.com/wp-content/uploads/2024/08/mdm1.webp',
            'treatment' => 'https://i0.wp.com/immaserilaris.com/wp-content/uploads/2024/06/rawatan@3x-100.webp',
            'spa' => 'https://i0.wp.com/immaserilaris.com/wp-content/uploads/2024/02/WhatsApp-Image-2024-02-08-at-6.51.09-PM.jpeg',
        ];

        $urls = [];

        foreach ($remote as $key => $url) {
            $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $filename = "{$key}.{$extension}";
            $filepath = "{$dir}/{$filename}";

            if (! file_exists($filepath)) {
                $content = @file_get_contents($url);

                if ($content !== false) {
                    file_put_contents($filepath, $content);
                }
            }

            $urls[$key] = file_exists($filepath)
                ? asset("storage/media/imma-about/{$filename}")
                : $url;
        }

        return $urls;
    }


    /**
     * @param array<string, mixed> $copy
     * @param array<string, string> $images
     */
    private function buildBody(array $copy, array $images): string
    {
        $hero = e($images['hero']);
        $founder = e($images['founder']);
        $treatment = e($images['treatment']);
        $spa = e($images['spa']);

        $services = '';

        foreach ($copy['services'] as $service) {
            $services .= <<<HTML
            <article class="imma-about-card">
                <div class="imma-about-card__icon">{$service['icon']}</div>
                <h3>{$service['title']}</h3>
                <p>{$service['text']}</p>
            </article>
HTML;
        }

        $vision = '';

        foreach ($copy['vision'] as $item) {
            $vision .= '<li>' . e($item) . '</li>';
        }

        $mission = '';

        foreach ($copy['mission'] as $item) {
            $mission .= '<li>' . e($item) . '</li>';
        }

        $badges = '';

        foreach ($copy['badges'] as $badge) {
            $badges .= '<span class="imma-about-badge">' . e($badge) . '</span>';
        }

        $stats = '';

        foreach ($copy['stats'] as $stat) {
            $stats .= <<<HTML
            <div class="imma-about-stat">
                <strong>{$stat['value']}</strong>
                <span>{$stat['label']}</span>
            </div>
HTML;
        }

        $careersCtaUrl = e($copy['careers_cta_url']);

        return <<<HTML
<div class="imma-about">
    <section class="imma-about-hero">
        <div class="imma-about-hero__media">
            <img src="{$hero}" alt="{$copy['img_hero_alt']}" loading="eager" width="720" height="540">
        </div>
        <div class="imma-about-hero__content">
            <span class="imma-about-tagline">{$copy['tagline']}</span>
            <h1>{$copy['title']}</h1>
            <p class="imma-about-subtitle">{$copy['subtitle']}</p>
            <p class="imma-about-lead">{$copy['lead']}</p>
            <div class="imma-about-badge-row">{$badges}</div>
        </div>
    </section>

    <div class="imma-about-stats">{$stats}</div>

    <section class="imma-about-section imma-about-highlight">
        <div class="imma-about-section__head">
            <span class="imma-about-kicker">{$copy['one_stop_kicker']}</span>
            <h2>{$copy['one_stop_title']}</h2>
        </div>
        <p>{$copy['one_stop_text']}</p>
    </section>

    <section class="imma-about-section imma-about-split">
        <div class="imma-about-split__media">
            <img src="{$founder}" alt="{$copy['img_founder_alt']}" loading="lazy" width="480" height="640">
        </div>
        <div class="imma-about-split__text">
            <div class="imma-about-section__head">
                <span class="imma-about-kicker">{$copy['story_kicker']}</span>
                <h2>{$copy['story_title']}</h2>
            </div>
            <p>{$copy['story_p1']}</p>
            <p>{$copy['story_p2']}</p>
        </div>
    </section>

    <section class="imma-about-section imma-about-gallery">
        <img src="{$treatment}" alt="{$copy['img_treatment_alt']}" loading="lazy" width="800" height="500">
        <div class="imma-about-gallery__stack">
            <img src="{$spa}" alt="{$copy['img_spa_alt']}" loading="lazy" width="400" height="240">
            <img src="{$hero}" alt="{$copy['img_location_alt']}" loading="lazy" width="400" height="240">
        </div>
    </section>

    <section class="imma-about-section">
        <div class="imma-about-section__head">
            <span class="imma-about-kicker">{$copy['services_kicker']}</span>
            <h2>{$copy['services_title']}</h2>
            <p>{$copy['services_intro']}</p>
        </div>
        <div class="imma-about-grid">{$services}</div>
    </section>

    <section class="imma-about-section imma-about-vm">
        <div class="imma-about-vm__box">
            <div class="imma-about-section__head">
                <span class="imma-about-kicker">{$copy['vision_kicker']}</span>
                <h2>{$copy['vision_title']}</h2>
            </div>
            <ul>{$vision}</ul>
        </div>
        <div class="imma-about-vm__box">
            <div class="imma-about-section__head">
                <span class="imma-about-kicker">{$copy['mission_kicker']}</span>
                <h2>{$copy['mission_title']}</h2>
            </div>
            <ul>{$mission}</ul>
        </div>
    </section>

    <section class="imma-about-section imma-about-info">
        <div class="imma-about-info__item">
            <h3>{$copy['facilities_title']}</h3>
            <p>{$copy['facilities_text']}</p>
        </div>
        <div class="imma-about-info__item">
            <h3>{$copy['hours_title']}</h3>
            <ul>
                <li>{$copy['hours_week']}</li>
                <li>{$copy['hours_sun']}</li>
            </ul>
        </div>
    </section>

    <!--IMMA_SPA_BRANCHES-->

    <section class="imma-about-section imma-about-cta">
        <div>
            <h2>{$copy['careers_title']}</h2>
            <p>{$copy['careers_text']}</p>
        </div>
        <a href="{$careersCtaUrl}">{$copy['careers_cta_label']}</a>
    </section>

    <footer class="imma-about-footer">
        <p>{$copy['footer']}</p>
    </footer>
</div>
HTML;
    }


    /**
     * @return array<string, mixed>
     */
    private function englishCopy(string $contactUrl, int $branchCount): array
    {
        return [
            'tagline' => '#Selagi tak cantik, jangan balik',
            'title' => 'IMMA Seri Laris',
            'subtitle' => 'SPA &amp; Aesthetic · Muslimah-friendly · Patuh Syariah',
            'lead' => 'Established on <strong>11 March 2014</strong>, IMMA Seri Laris offers comprehensive <strong>aesthetic spa &amp; medispa</strong> services including aromatherapy massage, traditional and therapeutic massage, steam bath (sauna), herbal compress, facial and body treatments, manicure &amp; pedicure, makeup artistry, wellness consultation, and postnatal care.',
            'badges' => ['Since 2014', 'Muslimah-friendly', 'Patuh Syariah', 'One Stop Service'],
            'stats' => [
                ['value' => '2014', 'label' => 'Established'],
                ['value' => (string) max(1, $branchCount), 'label' => 'Locations'],
                ['value' => '6+', 'label' => 'Service areas'],
                ['value' => '100%', 'label' => 'Muslimah-friendly'],
            ],
            'one_stop_kicker' => 'Our concept',
            'one_stop_title' => 'One Stop Service',
            'one_stop_text' => 'Our concept is designed as a relaxed, family-friendly spa where mothers and children are welcome. As a <strong>one-stop Muslimah &amp; Islamic beauty centre</strong>, we offer beauty and body care services plus Sharia-compliant products under one roof — at attractive prices.',
            'story_kicker' => 'Since 2014',
            'story_title' => 'Our Story',
            'story_p1' => 'Inspired by our founder <strong>Madam IMMA</strong>, who brings strong passion and professional qualifications in beauty and wellness. With certified training and hands-on experience, we are confident in delivering services that delight and satisfy every client.',
            'story_p2' => 'Beauty and grooming are essential needs — especially for women who value their appearance at work and on special occasions. This trend strengthens IMMA Seri Laris as a competitive, sustainable brand in the Malaysian market.',
            'services_kicker' => 'What we offer',
            'services_title' => 'Services',
            'services_intro' => 'Comprehensive beauty, wellness and aesthetic care under one Muslimah-friendly roof.',
            'services' => [
                ['icon' => '✨', 'title' => 'Aesthetic &amp; Laser', 'text' => 'Aesthetic treatments, Pico Laser &amp; eyewash for radiant, confident skin.'],
                ['icon' => '💄', 'title' => 'Bridal &amp; Events', 'text' => 'Bridal and event makeup by skilled artists for your special day.'],
                ['icon' => '🧖', 'title' => 'SPA &amp; Body Care', 'text' => 'SPA, facial, body &amp; nail care in a calm, comfortable setting.'],
                ['icon' => '🛍️', 'title' => 'Cosmetik', 'text' => 'Halal cosmetik products aligned with current beauty trends.'],
                ['icon' => '🏠', 'title' => 'Mobile Spa', 'text' => 'Home-to-home treatments, especially postnatal care.'],
                ['icon' => '🌿', 'title' => 'Wellness', 'text' => 'Aromatherapy, traditional massage, sauna &amp; herbal compress therapies.'],
            ],
            'vision_kicker' => 'Looking ahead',
            'vision_title' => 'Vision',
            'vision' => [
                'Lead provider of SPA, salon &amp; aesthetic services',
                'Skills training centre for beauty professionals',
                'Nationwide wholesaler of beauty products',
            ],
            'mission_kicker' => 'What we do',
            'mission_title' => 'Mission',
            'mission' => [
                'Provide SPA, aesthetic &amp; salon centres that are Sharia-compliant (Muslimah)',
                'Wholesale and retail Sharia-compliant spa &amp; salon products',
            ],
            'facilities_title' => 'Facilities',
            'facilities_text' => 'Comfortable treatment rooms and prayer facilities for your convenience before or after your session.',
            'hours_title' => 'Operating Hours',
            'hours_week' => '<strong>Monday – Saturday:</strong> 11:00 AM – 9:00 PM',
            'hours_sun' => '<strong>Sunday:</strong> 11:00 AM – 6:00 PM',
            'careers_title' => 'Careers',
            'careers_text' => 'Interested in joining our team? Email <a href="mailto:booking@immaserilaris.com">booking@immaserilaris.com</a> or reach out through our contact page.',
            'careers_cta_url' => $contactUrl,
            'careers_cta_label' => 'Contact Us',
            'footer' => 'IMMA Seri Laris — treatment, spa &amp; aesthetic services at <a href="https://immaserilaris.com" target="_blank" rel="noopener">immaserilaris.com</a>',
            'img_hero_alt' => 'IMMA Seri Laris SPA & Aesthetic centre',
            'img_founder_alt' => 'Madam IMMA — founder of IMMA Seri Laris',
            'img_treatment_alt' => 'Aesthetic and spa treatments at IMMA Seri Laris',
            'img_spa_alt' => 'Relaxing spa environment',
            'img_location_alt' => 'IMMA Seri Laris building',
        ];
    }


    /**
     * @return array<string, mixed>
     */
    private function malayCopy(string $contactUrl, int $branchCount): array
    {
        return [
            'tagline' => '#Selagi tak cantik, jangan balik',
            'title' => 'IMMA Seri Laris',
            'subtitle' => 'SPA &amp; Estetik · Mesra Muslimah · Patuh Syariah',
            'lead' => 'Ditubuhkan pada <strong>11 Mac 2014</strong>, IMMA Seri Laris menawarkan perkhidmatan <strong>spa estetik &amp; medispa</strong> seperti urutan aromaterapi, urutan tradisional dan terapeutik, mandi wap (sauna), tungku herba, rawatan muka dan badan, rawatan kuku tangan dan kaki, solekan, khidmat runding kesihatan &amp; kecantikan, serta rawatan ibu selepas bersalin.',
            'badges' => ['Sejak 2014', 'Mesra Muslimah', 'Patuh Syariah', 'One Stop Service'],
            'stats' => [
                ['value' => '2014', 'label' => 'Ditubuhkan'],
                ['value' => (string) max(1, $branchCount), 'label' => 'Lokasi'],
                ['value' => '6+', 'label' => 'Bidang perkhidmatan'],
                ['value' => '100%', 'label' => 'Mesra Muslimah'],
            ],
            'one_stop_kicker' => 'Konsep kami',
            'one_stop_title' => 'One Stop Service',
            'one_stop_text' => 'Konsep santai untuk ibu dan anak datang ke spa. Sebagai <strong>pusat kecantikan Muslimah &amp; Islamik one-stop</strong>, kami menawarkan rawatan kecantikan, rawatan badan dan produk patuh Syariah dalam satu bumbung — pada harga yang menarik.',
            'story_kicker' => 'Sejak 2014',
            'story_title' => 'Latar Belakang',
            'story_p1' => 'Diilhamkan oleh pemilik <strong>Puan IMMA</strong> yang mempunyai minat serta kemahiran tinggi dalam bidang kecantikan dan wellness. Dengan kelayakan profesional dan latihan yang diikuti, kami yakin dapat memberi perkhidmatan yang memuaskan hati pelanggan.',
            'story_p2' => 'Penjagaan diri dan penampilan amat penting terutama bagi wanita — sama ada di tempat kerja atau majlis istimewa. Ini mewujudkan kelebihan daya saing IMMA Seri Laris di pasaran Malaysia.',
            'services_kicker' => 'Apa yang kami tawarkan',
            'services_title' => 'Perkhidmatan',
            'services_intro' => 'Penjagaan kecantikan, wellness dan estetik menyeluruh di bawah satu bumbung mesra Muslimah.',
            'services' => [
                ['icon' => '✨', 'title' => 'Estetik &amp; Laser', 'text' => 'Rawatan estetik, Pico Laser &amp; eyewash untuk kulit lebih berseri.'],
                ['icon' => '💄', 'title' => 'Pengantin &amp; Majlis', 'text' => 'Solekan pengantin dan majlis oleh pakar solek berpengalaman.'],
                ['icon' => '🧖', 'title' => 'SPA &amp; Penjagaan Badan', 'text' => 'SPA, facial, rawatan badan &amp; kuku dalam suasana tenang.'],
                ['icon' => '🛍️', 'title' => 'Cosmetik', 'text' => 'Produk cosmetik halal mengikut trend kecantikan semasa.'],
                ['icon' => '🏠', 'title' => 'Mobile Spa', 'text' => 'Rawatan dari rumah ke rumah, terutama lepas bersalin.'],
                ['icon' => '🌿', 'title' => 'Wellness', 'text' => 'Aromaterapi, urutan tradisional, sauna &amp; tungku herba.'],
            ],
            'vision_kicker' => 'Visi ke hadapan',
            'vision_title' => 'Visi',
            'vision' => [
                'Menjadi peneraju perkhidmatan SPA, salon &amp; estetik',
                'Menjadi pusat latihan kemahiran',
                'Menjadi pemborong produk kecantikan di seluruh Malaysia',
            ],
            'mission_kicker' => 'Apa yang kami lakukan',
            'mission_title' => 'Misi',
            'mission' => [
                'Menyediakan pusat SPA, estetik &amp; salon berkonsepkan patuh Syariah (Muslimah)',
                'Menjadi pemborong dan peruncit produk spa &amp; salon patuh Syariah',
            ],
            'facilities_title' => 'Fasiliti',
            'facilities_text' => 'Ruang rawatan yang selesa serta kemudahan solat untuk keselesaan anda sebelum atau selepas sesi rawatan.',
            'hours_title' => 'Waktu Operasi',
            'hours_week' => '<strong>Isnin – Sabtu:</strong> 11:00 pagi – 9:00 malam',
            'hours_sun' => '<strong>Ahad:</strong> 11:00 pagi – 6:00 petang',
            'careers_title' => 'Kerjaya',
            'careers_text' => 'Berminat menyertai pasukan kami? E-mel <a href="mailto:booking@immaserilaris.com">booking@immaserilaris.com</a> atau hubungi kami melalui halaman contact.',
            'careers_cta_url' => $contactUrl,
            'careers_cta_label' => 'Hubungi Kami',
            'footer' => 'IMMA Seri Laris — rawatan, spa &amp; estetik di <a href="https://immaserilaris.com" target="_blank" rel="noopener">immaserilaris.com</a>',
            'img_hero_alt' => 'Pusat SPA & Estetik IMMA Seri Laris',
            'img_founder_alt' => 'Puan IMMA — pengasas IMMA Seri Laris',
            'img_treatment_alt' => 'Rawatan estetik dan spa di IMMA Seri Laris',
            'img_spa_alt' => 'Suasana spa yang selesa',
            'img_location_alt' => 'Bangunan IMMA Seri Laris',
        ];
    }

    private function activeBranchCount(): int
    {
        if (! app('modules')->isEnabled('SpaBranch')) {
            return 1;
        }

        return SpaBranch::query()->where('is_active', true)->count();
    }
}
