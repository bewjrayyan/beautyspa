<?php

namespace Modules\Storefront\Support;

class GoogleReviewsSettings
{
    public static function enabled(): bool
    {
        return (bool) setting('storefront_google_reviews_section_enabled');
    }


    public static function forHomePage(): ?array
    {
        if (! self::enabled()) {
            return null;
        }

        $rating = (float) (setting('storefront_google_reviews_rating') ?: 3.75);
        $items = self::items();
        $metrics = self::decodeMetrics(setting('storefront_google_reviews_metrics'));
        $reviewCount = (int) (setting('storefront_google_reviews_review_count') ?: 1297);

        return [
            'title' => setting('storefront_google_reviews_section_title') ?: 'Google Reviews',
            'rating' => $rating,
            'ratingDisplay' => number_format($rating, 2),
            'reviewCount' => $reviewCount,
            'stars' => self::starBreakdown($rating),
            'items' => $items,
            'metrics' => $metrics,
        ];
    }


    public static function decodeItems(?string $json): array
    {
        $items = json_decode($json ?: '[]', true);

        if (! is_array($items)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($item) {
            if (! is_array($item) || empty($item['author'])) {
                return null;
            }

            return [
                'author' => $item['author'],
                'date' => $item['date'] ?? '',
                'rating' => min(5, max(1, (int) ($item['rating'] ?? 5))),
                'text' => $item['text'] ?? '',
                'likes' => (int) ($item['likes'] ?? 0),
            ];
        }, $items)));
    }


    public static function items(?string $json = null): array
    {
        $items = self::decodeItems($json ?? setting('storefront_google_reviews_items'));

        return $items !== [] ? $items : self::defaultItems();
    }


    public static function defaultItems(): array
    {
        return [
            [
                'author' => 'Siti Aminah',
                'date' => '18 APR 2025',
                'rating' => 5,
                'text' => 'Facial memang best gila! Staff sporting, kedai pun bersih. Confirm datang lagi.',
                'likes' => 298,
            ],
            [
                'author' => 'Nurul Izzah',
                'date' => '12 APR 2025',
                'rating' => 5,
                'text' => 'Book online senang je. Beautician explain step by step — very professional, highly recommended.',
                'likes' => 241,
            ],
            [
                'author' => 'Michelle Tan',
                'date' => '05 APR 2025',
                'rating' => 4,
                'text' => 'Harga ok for the result. Sabtu agak lama sikit tunggu, but worth it lah.',
                'likes' => 187,
            ],
            [
                'author' => 'Farah Liyana',
                'date' => '28 MAR 2025',
                'rating' => 5,
                'text' => 'Lepas hydrating facial, kulit rasa lembut giler. Suasana tenang, service tip top.',
                'likes' => 165,
            ],
            [
                'author' => 'Priya Devi',
                'date' => '20 MAR 2025',
                'rating' => 4,
                'text' => 'Good experience overall. Products bau sedap, tak pedih. Parking pun senang.',
                'likes' => 142,
            ],
            [
                'author' => 'Chen Wei Ling',
                'date' => '14 MAR 2025',
                'rating' => 5,
                'text' => 'First time here — consultation dulu baru start. Package clear, no hidden charge.',
                'likes' => 128,
            ],
            [
                'author' => 'Aisyah Rahman',
                'date' => '08 MAR 2025',
                'rating' => 3,
                'text' => 'Treatment ok je. Bilik selesa, cuma harap boleh start on time next visit.',
                'likes' => 76,
            ],
            [
                'author' => 'Emily Wong',
                'date' => '01 MAR 2025',
                'rating' => 5,
                'text' => 'Customer tetap 6 bulan. Quality konsisten every visit — best team in town!',
                'likes' => 312,
            ],
            [
                'author' => 'Hafizah Omar',
                'date' => '22 FEB 2025',
                'rating' => 4,
                'text' => 'Body scrub + massage combo syok gila. Next time nak try premium package pulak.',
                'likes' => 119,
            ],
            [
                'author' => 'Jessica Lim',
                'date' => '15 FEB 2025',
                'rating' => 5,
                'text' => 'After 3 sessions nampak result. Staff follow up WhatsApp for aftercare — so helpful!',
                'likes' => 203,
            ],
            [
                'author' => 'Zara Ibrahim',
                'date' => '09 FEB 2025',
                'rating' => 4,
                'text' => 'Kemasan bersih, front desk sopan. Agak mahal sikit tapi you get what you pay for.',
                'likes' => 94,
            ],
            [
                'author' => 'David Ng',
                'date' => '02 FEB 2025',
                'rating' => 5,
                'text' => 'Beli voucher untuk wife. Dia suka spa day package. Redeem pun senang.',
                'likes' => 88,
            ],
            [
                'author' => 'Amira Hassan',
                'date' => '25 JAN 2025',
                'rating' => 4,
                'text' => 'Acne series memang membantu. Takde hard selling masa bayar — appreciate that.',
                'likes' => 156,
            ],
            [
                'author' => 'Karen Lee',
                'date' => '18 JAN 2025',
                'rating' => 5,
                'text' => 'Eyebrow shaping & lash lift on point. Natural look, tahan lama weeks.',
                'likes' => 177,
            ],
            [
                'author' => 'Nadia Yusof',
                'date' => '10 JAN 2025',
                'rating' => 4,
                'text' => 'Kerusi recline selesa, music soothing. Rasa di-manjakan dari mula sampai habis.',
                'likes' => 101,
            ],
        ];
    }


    public static function decodeMetrics(?string $json): array
    {
        $metrics = json_decode($json ?: '[]', true);

        if (! is_array($metrics)) {
            return self::defaultMetrics();
        }

        $parsed = array_values(array_filter(array_map(function ($metric) {
            if (! is_array($metric) || empty($metric['label'])) {
                return null;
            }

            return [
                'label' => $metric['label'],
                'percent' => min(100, max(0, (int) ($metric['percent'] ?? 0))),
                'sentiment' => $metric['sentiment'] ?? 'Average',
            ];
        }, $metrics)));

        return $parsed !== [] ? $parsed : self::defaultMetrics();
    }


    public static function defaultMetrics(): array
    {
        return [
            ['label' => 'Professionalism', 'percent' => 92, 'sentiment' => 'Great'],
            ['label' => 'Efficiency', 'percent' => 74, 'sentiment' => 'Good'],
            ['label' => 'Response time', 'percent' => 55, 'sentiment' => 'So-so'],
        ];
    }


    /**
     * @return array{full: int, half: bool, empty: int}
     */
    public static function starBreakdown(float $rating): array
    {
        $full = (int) floor($rating);
        $fraction = $rating - $full;
        $half = $fraction >= 0.25 && $fraction < 0.75;
        $extra = ($fraction >= 0.75) ? 1 : 0;
        $full = min(5, $full + $extra);
        $empty = 5 - $full - ($half ? 1 : 0);

        return [
            'full' => $full,
            'half' => $half,
            'empty' => max(0, $empty),
        ];
    }
}
