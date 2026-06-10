<?php

/**
 * Release changelog shown in Admin → Settings → System after each version update.
 * Add a new entry whenever app/AestheticCart.php VERSION is bumped.
 */
return [
    '4.7.30' => [
        'en' => [
            'summary' => 'Fix empty PWA color picker console warnings in admin settings.',
            'changes' => [
                'Color inputs use valid #rrggbb defaults when database values are empty.',
                'Unset PWA colors are not submitted on save until you pick a value.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki amaran konsol pemilih warna PWA kosong dalam tetapan admin.',
            'changes' => [
                'Input warna guna default #rrggbb sah apabila nilai pangkalan data kosong.',
                'Warna PWA yang belum diset tidak dihantar semasa simpan sehingga anda pilih nilai.',
            ],
        ],
    ],
    '4.7.29' => [
        'en' => [
            'summary' => 'General settings single-column layout and version changelog in System tab.',
            'changes' => [
                'General tab fields stack in one column instead of two.',
                'System tab shows release notes for the installed version and pending updates.',
                'Version update success messages include what changed.',
            ],
        ],
        'ms' => [
            'summary' => 'Susun atur satu lajur untuk tab General dan changelog versi dalam tab System.',
            'changes' => [
                'Medan tab General disusun satu lajur, bukan dua.',
                'Tab System memaparkan nota release untuk versi terpasang dan kemas kini menunggu.',
                'Mesej berjaya kemas kini versi menyertakan perubahan terkini.',
            ],
        ],
    ],
    '4.7.28' => [
        'en' => [
            'summary' => 'Improve settings toggle contrast and sync footer version.',
            'changes' => [
                'Toggle OFF uses light grey and ON uses dark grey for clearer contrast.',
                'Admin footer version reads from app/AestheticCart.php.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki kontras toggle tetapan dan selaraskan versi footer.',
            'changes' => [
                'Toggle OFF kelabu cerah dan ON kelabu gelap untuk kontras lebih jelas.',
                'Versi footer admin dibaca dari app/AestheticCart.php.',
            ],
        ],
    ],
    '4.7.26' => [
        'en' => [
            'summary' => 'Two-column layouts across admin settings tabs.',
            'changes' => [
                'Store, mail, payments, shipping, and integration tabs use a two-column field grid.',
                'Taller settings sidebar for easier navigation.',
            ],
        ],
        'ms' => [
            'summary' => 'Susun atur dua lajur merentas tab tetapan admin.',
            'changes' => [
                'Tab kedai, mel, pembayaran, penghantaran dan integrasi menggunakan grid dua lajur.',
                'Sidebar tetapan lebih tinggi untuk navigasi lebih mudah.',
            ],
        ],
    ],
    '4.7.25' => [
        'en' => [
            'summary' => 'Improve storefront and admin load performance.',
            'changes' => [
                'Homepage queries gated by visible sections; hero and widgets load lazily.',
                'Dashboard and analytics cached; warm production caches from System tab.',
            ],
        ],
        'ms' => [
            'summary' => 'Tingkatkan prestasi muat storefront dan admin.',
            'changes' => [
                'Query laman utama digated mengikut seksyen; hero dan widget dimuatkan secara lazy.',
                'Dashboard dan analitik dicache; panaskan cache production dari tab System.',
            ],
        ],
    ],
    '4.7.24' => [
        'en' => [
            'summary' => 'Ship public/build assets in Git for shared hosting.',
            'changes' => [
                'Production deploy no longer requires npm on the server.',
            ],
        ],
        'ms' => [
            'summary' => 'Sertakan aset public/build dalam Git untuk shared hosting.',
            'changes' => [
                'Deploy production tidak lagi memerlukan npm pada pelayan.',
            ],
        ],
    ],
    '4.7.23' => [
        'en' => [
            'summary' => 'Fix settings nav icons and false unsaved state.',
            'changes' => [
                'Correct Font Awesome 4 icon names in settings navigation.',
                'Maintenance tab no longer shows false unsaved changes on load.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki ikon navigasi tetapan dan status belum simpan palsu.',
            'changes' => [
                'Nama ikon Font Awesome 4 yang betul dalam navigasi tetapan.',
                'Tab Penyelenggaraan tidak lagi menunjukkan perubahan belum disimpan palsu semasa muat.',
            ],
        ],
    ],
    '4.7.22' => [
        'en' => [
            'summary' => 'Modernize admin Settings page UI/UX.',
            'changes' => [
                'New sidebar with search, collapsible groups, and unsaved-changes badge.',
                'Refreshed settings panels and form styling.',
            ],
        ],
        'ms' => [
            'summary' => 'Modenkan UI/UX halaman Tetapan admin.',
            'changes' => [
                'Sidebar baharu dengan carian, kumpulan boleh lipat dan lencana perubahan belum disimpan.',
                'Panel tetapan dan gaya borang diperbaharui.',
            ],
        ],
    ],
];
