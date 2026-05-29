<?php

namespace Modules\Product\Services;

use Modules\Product\Entities\Product;

class RandomProductReviewGenerator
{
    /** @var list<string> */
    private array $firstNames = [
        'Siti', 'Nurul', 'Farah', 'Aisyah', 'Amira', 'Hafizah', 'Nadia', 'Zara', 'Priya', 'Jessica',
        'Michelle', 'Karen', 'Chen', 'David', 'Adam', 'Hakim', 'Irfan', 'Liyana', 'Suhaila', 'Aina',
        'Balqis', 'Damia', 'Elena', 'Fatin', 'Hanna', 'Izzati', 'Jannah', 'Khairul', 'Laila', 'Mira',
    ];

    /** @var list<string> */
    private array $lastNames = [
        'Aminah', 'Izzah', 'Hassan', 'Rahman', 'Tan', 'Lim', 'Ng', 'Omar', 'Devi', 'Wong',
        'Azman', 'Yusof', 'Ibrahim', 'Kamal', 'Salleh', 'Mokhtar', 'Zainal', 'Rosli', 'Hamid', 'Said',
    ];


    /**
     * @return list<array{name: string, rating: int, comment: string}>
     */
    public function generate(Product $product, int $count = 10): array
    {
        $theme = $this->detectTheme($product);
        $usedNames = [];
        $usedComments = [];
        $reviews = [];

        for ($i = 0; $i < $count; $i++) {
            $comment = $this->randomComment($theme);

            while (isset($usedComments[$comment]) && count($usedComments) < 500) {
                $comment = $this->randomComment($theme);
            }

            $usedComments[$comment] = true;

            $reviews[] = [
                'name' => $this->randomName($usedNames),
                'rating' => $this->randomRating(),
                'comment' => $comment,
            ];
        }

        return $reviews;
    }


    private function detectTheme(Product $product): string
    {
        $haystack = mb_strtolower($product->name . ' ' . $product->slug);

        if (preg_match('/benang|thread|pdo|fox eyes|dimple|hidung|nose|cog double chin|double chin|v-shape|v shape/i', $haystack)) {
            return 'thread';
        }

        if (preg_match('/surgery|facelift|labiaplasty|nasal tip|eyebag surgery|subbrow|lips reduction|microblading|eyeliner embroidery|lip blush/i', $haystack)) {
            return 'surgery';
        }

        if (preg_match('/lipo-botox|lipo & botox|lipo botox/i', $haystack)) {
            return 'lipo_botox';
        }

        if (preg_match('/skinny shot|lipotropic|luna slim|aura slim|slimming injection/i', $haystack)) {
            return 'slim';
        }

        if (preg_match('/\blipo\b|lipo lengan|lipo peha|lipo perut|lipo double chin/i', $haystack)) {
            return 'lipo';
        }

        if (preg_match('/botox|wrinkle relax|kedutan|bunga hidung|celahan ketiak|rahang botox/i', $haystack)) {
            return 'botox';
        }

        if (preg_match('/skin booster|rejuran|profhilo|juvelook|placentex|luhilo|baby booster|rinascita|pigment treatment|keloid|salmon dna/i', $haystack)) {
            return 'booster';
        }

        if (preg_match('/filler|boddy|smile line|eyebag|miss v|chin filler|lips filler/i', $haystack)) {
            return 'filler';
        }

        if (preg_match('/combo drip|combo-drip|9 drip|laku keras|highway premium|snow perl|snow pearl/i', $haystack)) {
            return 'combo_drip';
        }

        if (preg_match('/whitening|drip|vit c|anak dara|detox injection|lumina|lumeire|snow pearl|aura diamond/i', $haystack)) {
            return 'drip';
        }

        if (preg_match('/pakej|pengantin|bridal|rahmah|queen jelita|cerah gebu|seri wajah/i', $haystack)) {
            return 'bridal';
        }

        if (preg_match('/manicure|pedicure|nail polish|remove nail|gel polish|nail art|foot spa|opi colour/i', $haystack)) {
            return 'nails';
        }

        if (preg_match('/cream cerah|cerah lipatan|lipatan ketiak/i', $haystack)) {
            return 'skincare';
        }

        if (preg_match('/body treatment|mandi bunga|mandi susu|body scrub|body bleaching|sauna|garam aura/i', $haystack)) {
            return 'body_spa';
        }

        if (preg_match('/tattoo laser|tattoo removal|color ink|black ink tattoo/i', $haystack)) {
            return 'tattoo_laser';
        }

        if (preg_match('/eye lash|eyelash|baby doll|volume look|classic 3d|4d natural|keratin lash|lash lift|russion|mega vollume/i', $haystack)) {
            return 'eyelash';
        }

        if (preg_match('/normal facial|3 in 1 facial|8 in 1 facial|facial treatment/i', $haystack)) {
            return 'facial';
        }

        if (preg_match('/vikeno|5 in 1|bio facial|lifting facial|brow lamination|nipple pink|volume look/i', $haystack)) {
            return 'spa_device';
        }

        if (preg_match('/carbon laser|pico laser|carbon peel|co2 laser/i', $haystack)) {
            return 'laser';
        }

        if (preg_match('/facial|hydra|prp|laser|pico|carbon|co2|spa|cleansing|ems|slim/i', $haystack)) {
            return 'facial';
        }

        return 'treatment';
    }


    /**
     * @param array<string, true> $usedNames
     */
    private function randomName(array &$usedNames): string
    {
        for ($attempt = 0; $attempt < 50; $attempt++) {
            $name = $this->firstNames[array_rand($this->firstNames)]
                . ' '
                . $this->lastNames[array_rand($this->lastNames)];

            if (! isset($usedNames[$name])) {
                $usedNames[$name] = true;

                return $name;
            }
        }

        return 'Customer ' . random_int(1000, 9999);
    }


    private function randomRating(): int
    {
        $roll = random_int(1, 100);

        if ($roll <= 45) {
            return 5;
        }

        if ($roll <= 80) {
            return 4;
        }

        if ($roll <= 92) {
            return 5;
        }

        return 3;
    }


    private function randomComment(string $theme): string
    {
        $openings = $this->openings();
        $experiences = $this->experiences($theme);
        $services = $this->serviceNotes();
        $closings = $this->closings();

        $parts = [
            $openings[array_rand($openings)],
            $experiences[array_rand($experiences)],
        ];

        if (random_int(0, 1) === 1) {
            $parts[] = $services[array_rand($services)];
        }

        if (random_int(0, 1) === 1) {
            $parts[] = $closings[array_rand($closings)];
        }

        shuffle($parts);

        return implode(' ', array_slice($parts, 0, random_int(2, 3)));
    }


    /** @return list<string> */
    private function openings(): array
    {
        return [
            'First time datang, overall experience memang ok.',
            'Baru je habis session, kulit rasa fresh gila.',
            'Book online senang, team respond cepat.',
            'Datang waktu appointment, treatment start selepas consult.',
            'Package grand opening memang worth it untuk try.',
            'Honestly tak menyesal pilih rawatan ni.',
            'Service dari awal sampai habis quite smooth.',
            'Kali kedua repeat, result lagi consistent.',
        ];
    }


    /** @return list<string> */
    private function experiences(string $theme): array
    {
        return match ($theme) {
            'thread' => [
                'Benang treatment nampak natural, tak over.',
                'Benang COG double chin — jawline lebih kemas.',
                'Fox eyes / lifting effect gradual tapi clear.',
                'Swelling manageable, beautician explain aftercare well.',
                'Thread placement kemas, muka nampak lebih defined.',
            ],
            'bridal' => [
                'Pakej pengantin lengkap — dari rambut ke kaki, puas hati.',
                'Queen Jelita package — glowing on wedding day.',
                'Cerah Gebu — kulit nampak cerah, makeup melekat cantik.',
                'Staff explain timeline clear, sesuai prep kahwin.',
                'Harga Rahmah worth it untuk pakej sebesar ni.',
            ],
            'nails' => [
                'Remove polish cepat, kuku tak rosak.',
                'Staff gentle, nails still healthy lepas session.',
                'Pedicure express — kaki rasa fresh, pantas.',
                'Foot spa best lepas penat berjalan.',
                'OPI colour cantik, tahan lama sikit.',
                'Tempat bersih, tools nampak hygienic.',
            ],
            'skincare' => [
                'Cream cerah lipatan — ketiak nampak lebih cerah lepas beberapa minggu.',
                'Formula lembut, sesuai kulit sensitif.',
                'Kurangkan iritasi lepas shave, puas hati.',
                'Melembap & mencerahkan, worth the price.',
                'Packaging ok, mudah apply setiap malam.',
            ],
            'body_spa' => [
                'Mandi bunga — wangi, badan rasa segar lepas treatment.',
                'Mandi susu — kulit lembut dan licin, best untuk dry skin.',
                'Body scrub — dead skin gone, smooth feeling.',
                'Body bleaching — tone lebih even selepas beberapa sesi.',
                'Sauna — relax, badan rasa ringan lepas session.',
            ],
            'tattoo_laser' => [
                'Color tattoo fade gradual lepas beberapa sesi.',
                'Black ink tattoo — fade lebih cepat berbanding color.',
                'Ala carte — first session ok, tak pedih sangat.',
                '6 session package — ink hitam kurang ketara setiap sesi.',
                'Staff explain recovery well, realistic expectations.',
                '12 session — result lebih clear, worth the package.',
            ],
            'eyelash' => [
                'Baby doll lashes — natural, wispy, cantik.',
                'Keratin lash lift — bulu mata naik, natural.',
                'Mega volume Russian — full tapi still wearable.',
                'Staff careful, comfortable throughout.',
                'Tahan lama, still look natural lepas 2 minggu.',
            ],
            'spa_device' => [
                'Vikeno 5 in 1 — kulit rasa lebih bersih lepas sesi pertama.',
                'Bio facial lifting — muka rasa lebih tegang, V-shape sikit.',
                'Package 6 session — texture improve gradual, worth it.',
                'Non-invasive, comfortable, tak pedih.',
                'Muka nampak lebih segar lepas treatment.',
            ],
            'laser' => [
                'Carbon laser — pori rasa lebih bersih, kulit cerah.',
                'Pico laser session comfortable, gradual improvement.',
                '6 session package worth it untuk jerawat & pigment.',
                'Ala carte good untuk first try, tak downtime panjang.',
                'Staff explain aftercare dengan jelas.',
            ],
            'facial' => [
                'Facial session buat pori rasa lebih bersih.',
                'Lepas laser/hydra, kulit glowing and softer.',
                'PRP session comfortable, texture improve within weeks.',
                'Deep cleansing buat muka rasa ringan, tak pedih.',
                'Carbon peel kurangkan oily skin, nampak matte sikit.',
            ],
            'combo_drip' => [
                'Combo 3x Highway Premium — jimat berbanding single.',
                'Anak Dara package 5x, kulit lebih cerah lepas sesi ke-3.',
                'Snow Pearl combo worth it untuk whitening goal.',
                'Aura Diamond 10x — staff ingat jadual, service konsisten.',
                'Detox Booster 3x — badan rasa ringan, repeat lagi.',
            ],
            'drip' => [
                'IV drip session selesa, badan rasa lebih fresh.',
                'Whitening drip buat kulit nampak cerah sikit.',
                'Detox booster rasa ringan lepas treatment.',
                'Anak dara drip best untuk first timer.',
                'Aura diamond drip — glowing effect gradual tapi clear.',
            ],
            'filler' => [
                'Lips filler natural, tak over-pump.',
                'Chin filler balance muka, V-shape subtle.',
                'Smile line kurang dalam, nampak lebih fresh.',
                'Eyebag area lebih cerah, tak pedih sangat.',
                'Body filler consultation clear, volume gradual.',
            ],
            'booster' => [
                'Profhilo buat kulit lebih glow dan tegang.',
                'Rejuran healer — texture improve lepas beberapa hari.',
                'Baby booster ringan, sesuai first timer.',
                'Juvelook lifting effect gradual, natural.',
                'Placentex session comfortable, kulit lebih lembap.',
            ],
            'botox' => [
                'Botox dahi — garis halus kurang, natural.',
                'Botox mata — crow feet lebih smooth.',
                'Smile line area lebih relaxed, tak stiff.',
                'Celahan ketiak — kurang peluh, confident.',
                'Rahang botox — jawline lebih defined sikit.',
            ],
            'lipo_botox' => [
                'Lipo perut 4 botol — gradual slimming, tak extreme.',
                'Lipo double chin — jawline lebih kemas sikit.',
                'Botox dahi — garis halus kurang, natural look.',
                'Botox tepi mata — crow feet lebih smooth.',
                'Combo lipo + botox consultation clear, worth it.',
            ],
            'lipo' => [
                'Lipo 1 botol — target area kecil, sesuai first try.',
                'Lipo lengan 2 botol — lengan lebih kemas lepas beberapa minggu.',
                'Lipo 4 session package — jimat berbanding single.',
                'Lipo peha — result gradual, staff explain aftercare.',
            ],
            'slim' => [
                'Lipotropic injection perut — rasa ringan lepas session.',
                'Luna Slim sesuai untuk target area kecil.',
                '4 sesi package worth it, gradual result.',
                'D.Chin injection — double chin kurang sikit.',
                'Aura Slim — badan rasa lebih fit lepas beberapa minggu.',
            ],
            'surgery' => [
                'Nasal tip — bentuk hidung lebih kemas, natural.',
                'Eyebag surgery — mata nampak lebih segar.',
                'Facelift minor — lifting subtle, recovery ok.',
                'Microblading kening — shape cantik, tak over.',
                'Lip blush embroidery — warna natural, puas hati.',
            ],
            default => [
                'Rawatan sesuai dengan concern yang saya explain.',
                'Result nampak lepas beberapa hari, gradual tapi ok.',
                'Comfort level bagus, tempat bersih dan wangi.',
                'Beautician sangat gentle, tak rush sangat.',
                'Effect tahan lama kalau ikut aftercare betul.',
            ],
        };
    }


    /** @return list<string> */
    private function serviceNotes(): array
    {
        return [
            'Staff friendly, explain step by step.',
            'Takde hard selling masa checkout — appreciate that.',
            'WhatsApp follow-up lepas treatment membantu.',
            'Sabtu agak ramai, tunggu sikit lama but still ok.',
            'Room selesa, privacy ok untuk treatment.',
            'Consultation free feel, tak pressure sangat.',
            'Reception helpful bila nak reschedule.',
            'Parking area senang, location easy to find.',
        ];
    }


    /** @return list<string> */
    private function closings(): array
    {
        return [
            'Will recommend to kawan.',
            'Confirm akan datang lagi next month.',
            'Highly recommended untuk first timer.',
            'Good value for the price offered.',
            'Overall puas hati dengan hasil.',
            'Maybe boleh improve timing start, lain ok.',
            'Best treatment experience so far this year.',
            'Terima kasih team Immaserilaris!',
        ];
    }
}
