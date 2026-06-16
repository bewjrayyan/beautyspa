<?php

/**
 * Release changelog shown in Admin → Settings → System after each version update.
 * Add a new entry whenever app/AestheticCart.php VERSION is bumped.
 */
return [
    '4.7.56' => [
        'en' => [
            'summary' => 'Security hardening against SQL injection, brute-force, spam, and payment abuse.',
            'changes' => [
                'Require checkout session ownership before canceling online payment orders.',
                'Reject CHIP webhooks when webhook secret is missing or signature is invalid.',
                'Add rate limits for login, password reset, checkout, and public forms.',
                'Replace raw SQL in product attribute filters with parameterized query bindings.',
                'Harden session cookies (SameSite lax, secure in production) and trusted proxy configuration.',
                'Fix Google reCAPTCHA settings validation typo so site key is required when enabled.',
                'Update docs/SECURITY.md and .env.example with production security checklist.',
            ],
        ],
        'ms' => [
            'summary' => 'Pengukuhan keselamatan terhadap SQL injection, brute-force, spam, dan penyalahgunaan bayaran.',
            'changes' => [
                'Wajibkan pemilikan sesi checkout sebelum membatalkan pesanan bayaran dalam talian.',
                'Tolak webhook CHIP jika rahsia webhook tiada atau tandatangan tidak sah.',
                'Tambah had kadar untuk log masuk, set semula kata laluan, checkout, dan borang awam.',
                'Ganti SQL mentah dalam penapis atribut produk dengan parameter binding.',
                'Kuatkan kuki sesi (SameSite lax, secure dalam production) dan konfigurasi proxy dipercayai.',
                'Baiki typo validasi tetapan Google reCAPTCHA supaya kunci laman diperlukan apabila diaktifkan.',
                'Kemas kini docs/SECURITY.md dan .env.example dengan senarai semak keselamatan production.',
            ],
        ],
    ],
    '4.7.55' => [
        'en' => [
            'summary' => 'Beautician portal CRM dashboard, pipeline improvements, and payment receipt in calendar preview.',
            'changes' => [
                'Add beautician portal CRM dashboard (My Dashboard) with the same operational view as admin, scoped to the logged-in specialist.',
                'Lock portal filters to the beautician and their branch so specialists only see their own appointments.',
                'Replace legacy kanban view with the operational pipeline progression board.',
                'Show recorded finish time on completed pipeline cards and limit each column to two visible cards with scroll.',
                'Link specialist names to availability pages and fix profile avatars on the availability screen.',
                'Show uploaded payment receipt proof in the calendar appointment preview drawer.',
                'Extend grant-admin-permissions command to cover portal create/edit permissions on existing roles.',
            ],
        ],
        'ms' => [
            'summary' => 'Papan pemuka CRM portal beautician, penambahbaikan pipeline, dan bukti resit dalam pratonton kalendar.',
            'changes' => [
                'Tambah papan pemuka CRM portal beautician (Dashboard Saya) dengan paparan operasi sama seperti admin, skop kepada pakar yang log masuk.',
                'Kunci penapis portal kepada beautician dan cawangan mereka supaya pakar hanya lihat temujanji sendiri.',
                'Gantikan paparan kanban lama dengan papan perkembangan pipeline operasi.',
                'Paparkan masa selesai pada kad pipeline yang lengkap dan hadkan setiap lajur kepada dua kad dengan skrol.',
                'Pautkan nama pakar ke halaman ketersediaan dan baiki avatar profil pada skrin ketersediaan.',
                'Paparkan bukti resit bayaran yang dimuat naik dalam laci pratonton temujanji kalendar.',
                'Lanjutkan arahan grant-admin-permissions untuk kebenaran create/edit portal pada peranan sedia ada.',
            ],
        ],
    ],
    '4.7.54' => [
        'en' => [
            'summary' => 'CRM dashboard polish, WhatsApp reminder fixes, and manual booking phone validation.',
            'changes' => [
                'Redesign CRM stats, pipeline cards, toolbar, and compact ledger with SaaS-style layout.',
                'Add admin date/month picker filter, fix CRM search, and show appointment dates on pipeline cards.',
                'Add customer and beautician WhatsApp reminder actions with activity logging and resend support.',
                'Send manual WhatsApp reminders immediately instead of falsely logging queued messages as sent.',
                'Fix manual booking phone validation on edit by normalizing to E.164 before server validation.',
                'Improve visit labels (1st, 2nd visit), payment status on completed cards, and Finish/Done wording.',
                'Inline Font Awesome in admin CSS to reduce console asset warnings.',
                'After deploy, run: php artisan treatment-reservation:grant-admin-permissions (grants create/edit if missing on existing roles).',
            ],
        ],
        'ms' => [
            'summary' => 'Penambahbaikan papan pemuka CRM, pembetulan peringatan WhatsApp, dan validasi telefon tempahan manual.',
            'changes' => [
                'Reka semula statistik CRM, kad pipeline, bar alat, dan lejar padat dengan susun atur gaya SaaS.',
                'Tambah penapis tarikh/bulan admin, baiki carian CRM, dan paparkan tarikh temujanji pada kad pipeline.',
                'Tambah tindakan peringatan WhatsApp pelanggan dan beautician dengan log aktiviti dan hantar semula.',
                'Hantar peringatan WhatsApp manual serta-merta dan elak log palsu untuk mesej yang masih dalam giliran.',
                'Baiki validasi telefon semasa edit tempahan manual dengan normalisasi E.164 sebelum pengesahan server.',
                'Perbaiki label lawatan (lawatan ke-1, ke-2), status bayaran kad selesai, dan istilah Selesai.',
                'Sertakan Font Awesome dalam CSS admin untuk kurangkan amaran aset di konsol.',
                'Selepas deploy, jalankan: php artisan treatment-reservation:grant-admin-permissions (beri kebenaran create/edit jika tiada pada peranan sedia ada).',
            ],
        ],
    ],
    '4.7.53' => [
        'en' => [
            'summary' => 'Treatment Reservations CRM dashboard with customer profiles and appointment reminders.',
            'changes' => [
                'Add CRM dashboard aligned with mockup: KPIs, pipeline, specialists, ledger, calendar agenda.',
                'Enrich agenda cards with duration, clinical context, loyalty tier, visit history, and inline alerts.',
                'Add specialist availability toggle, WhatsApp quick actions, and pipeline drag-and-drop.',
                'Add customer CRM profile drawer with visit history, upcoming bookings, and reminder queue.',
                'Add manual WhatsApp appointment reminders with status badges and activity logging.',
                'Add CRM polish: date-filter-aware labels, permission gates, search UX, and validation command.',
            ],
        ],
        'ms' => [
            'summary' => 'Papan pemuka CRM Tempahan Rawatan dengan profil pelanggan dan peringatan temujanji.',
            'changes' => [
                'Tambah papan pemuka CRM selaras mockup: KPI, pipeline, pakar, lejar, agenda kalendar.',
                'Perkaya kad agenda dengan tempoh, konteks klinikal, tier loyalty, sejarah lawatan, dan amaran inline.',
                'Tambah togol ketersediaan pakar, tindakan WhatsApp pantas, dan seret-lepas pipeline.',
                'Tambah laci profil pelanggan CRM dengan sejarah lawatan, temujanji akan datang, dan giliran peringatan.',
                'Tambah peringatan temujanji WhatsApp manual dengan lencana status dan log aktiviti.',
                'Tambah polish CRM: label ikut penapis tarikh, kawalan kebenaran, carian, dan arahan pengesahan.',
            ],
        ],
    ],
    '4.7.52' => [
        'en' => [
            'summary' => 'Hotfix for production 500 errors after v4.7.51.',
            'changes' => [
                'Fix duplicate Cart import in CheckoutController that could fatal-error checkout routes.',
                'Harden portal preview helpers so storefront and admin keep working if preview services are unavailable.',
                'Register AdminPortalPreview earlier and only apply effective_admin_user on admin requests.',
            ],
        ],
        'ms' => [
            'summary' => 'Hotfix ralat 500 production selepas v4.7.51.',
            'changes' => [
                'Baiki import Cart pendua dalam CheckoutController yang boleh menyebabkan fatal error pada checkout.',
                'Kukuhkan helper pratonton portal supaya storefront dan admin kekal berfungsi jika servis pratonton tidak tersedia.',
                'Daftar AdminPortalPreview lebih awal dan gunakan effective_admin_user hanya pada permintaan admin.',
            ],
        ],
    ],
    '4.7.51' => [
        'en' => [
            'summary' => 'Beautician job titles master data, portal preview, and admin UX fixes.',
            'changes' => [
                'Add Job titles CRUD under Beauticians with permissions, sidebar submenu, and 29 default titles seeded.',
                'Load beautician Job Title field from master data using a standard dropdown instead of Selectize.',
                'Add Open beautician portal preview from the beautician edit form with role-based sidebar menu visibility.',
                'Auto-format job title names to Title Case on blur and save.',
                'Fix beautician portal preview 404, job titles DataTable loading, and table route selector mismatch.',
            ],
        ],
        'ms' => [
            'summary' => 'Master data jawatan beautician, pratonton portal, dan pembetulan UX admin.',
            'changes' => [
                'Tambah CRUD Jawatan beautician di bawah Beauticians dengan kebenaran, submenu sidebar, dan 29 jawatan lalai.',
                'Medan Jawatan beautician kini memuatkan senarai dari master data menggunakan dropdown standard.',
                'Tambah pratonton portal beautician dari borang sunting dengan menu sidebar ikut peranan pengguna portal.',
                'Auto-format nama jawatan kepada Title Case semasa blur dan simpan.',
                'Baiki 404 pratonton portal, jadual Job titles kosong, dan pemilih route DataTable.',
            ],
        ],
    ],
    '4.7.50' => [
        'en' => [
            'summary' => 'White sidebar menu labels and section headings in the admin panel.',
            'changes' => [
                'Style sidebar group headings (System, Content) with the menu-title class in white.',
                'Set all main and submenu sidebar link text to white for better contrast on custom sidebar colours.',
            ],
        ],
        'ms' => [
            'summary' => 'Label menu sidebar putih dan tajuk bahagian dalam panel admin.',
            'changes' => [
                'Gaya tajuk kumpulan sidebar (Sistem, Kandungan) dengan kelas menu-title berwarna putih.',
                'Set semua teks pautan menu utama dan submenu sidebar kepada putih untuk kontras lebih baik.',
            ],
        ],
    ],
    '4.7.49' => [
        'en' => [
            'summary' => 'Configurable admin sidebar colours and wider user account edit layout.',
            'changes' => [
                'Add sidebar background and accent colour pickers under Settings → Logo for admin panel branding.',
                'Apply saved sidebar colours across the admin menu, header, and submenu via CSS variables.',
                'Widen user create/edit main panel to an 80/20 layout so form tabs get more horizontal space.',
            ],
        ],
        'ms' => [
            'summary' => 'Warna sidebar admin boleh dikonfigurasi dan layout sunting akaun pengguna lebih lebar.',
            'changes' => [
                'Tambah pemilih warna latar dan aksen sidebar di Tetapan → Logo untuk penjenamaan panel admin.',
                'Guna warna sidebar tersimpan pada menu admin, header, dan submenu melalui pembolehubah CSS.',
                'Lebarkan panel utama cipta/sunting pengguna kepada layout 80/20 supaya tab borang dapat lebih ruang.',
            ],
        ],
    ],
    '4.7.48' => [
        'en' => [
            'summary' => 'Modernized admin users index, create, and edit account flows.',
            'changes' => [
                'Refresh users list with stats, role chips, avatars, and improved search layout.',
                'Redesign create and edit account pages with hero headers, top tab navigation, and sidebar tips.',
                'Add password strength meter, auto-generate password, and modern new-password tab for user accounts.',
            ],
        ],
        'ms' => [
            'summary' => 'Pengurusan pengguna admin baharu untuk senarai, cipta, dan sunting akaun.',
            'changes' => [
                'Kemas kini senarai pengguna dengan statistik, cip peranan, avatar, dan susun atur carian lebih baik.',
                'Reka bentuk semula halaman cipta dan sunting akaun dengan hero, tab atas, dan tip sidebar.',
                'Tambah penunjuk kekuatan kata laluan, jana automatik, dan tab kata laluan baharu yang moden.',
            ],
        ],
    ],
    '4.7.47' => [
        'en' => [
            'summary' => 'Loyalty stamp cards, CHIP checkout improvements, and per-method processing fees.',
            'changes' => [
                'Add stamp card programs with admin CRUD, customer account progress, redemption codes, and counter verification.',
                'Show stamp and loyalty rewards on order complete; award stamps automatically when orders are placed.',
                'CHIP checkout sends real product line items, per-method surcharges (FPX/card/Atome), and checkout fee display.',
            ],
        ],
        'ms' => [
            'summary' => 'Kad setem keahlian, penambahbaikan checkout CHIP, dan yuran pemprosesan setiap kaedah.',
            'changes' => [
                'Tambah program kad setem dengan CRUD admin, kemajuan akaun pelanggan, kod tebusan, dan pengesahan kaunter.',
                'Paparkan ganjaran setem dan mata pada pesanan selesai; anugerah setem automatik apabila pesanan dibuat.',
                'Checkout CHIP hantar baris produk sebenar, caj tambahan setiap kaedah (FPX/kad/Atome), dan paparan yuran checkout.',
            ],
        ],
    ],
    '4.7.46' => [
        'en' => [
            'summary' => 'Checkout reliability fixes, loyalty tier card preview, and cart translations.',
            'changes' => [
                'Fix checkout Alpine errors, phone E.164 validation, and offline payment (COD/bank transfer) completion in one step.',
                'Show editable phone field for logged-in customers; fix payment-method guard using raw order slug.',
                'Loyalty tier edit live preview uses real credit-card dimensions; empty cart BM copy for browsing treatments.',
            ],
        ],
        'ms' => [
            'summary' => 'Pembaikan checkout, pratonton kad peringkat keahlian, dan terjemahan troli.',
            'changes' => [
                'Baiki ralat Alpine checkout, validasi telefon E.164, dan lengkapkan bayaran offline (COD/pemindahan bank) dalam satu langkah.',
                'Paparkan medan telefon boleh sunting untuk pelanggan log masuk; betulkan guard kaedah bayaran menggunakan slug pesanan.',
                'Pratonton sunting peringkat guna saiz kad kredit sebenar; salinan BM troli kosong untuk cari rawatan.',
            ],
        ],
    ],
    '4.7.45' => [
        'en' => [
            'summary' => 'Polish loyalty tier edit page translations and preview labels.',
            'changes' => [
                'Use translated tier names in edit breadcrumbs and live preview.',
                'Replace monospace slug display with labelled slug meta; align members label with tiers table.',
            ],
        ],
        'ms' => [
            'summary' => 'Perbaiki terjemahan dan pratonton halaman sunting peringkat keahlian.',
            'changes' => [
                'Guna nama peringkat terjemahan dalam breadcrumb dan pratonton langsung.',
                'Ganti paparan slug monospace dengan label Slug; selaraskan label ahli dengan jadual peringkat.',
            ],
        ],
    ],
    '4.7.44' => [
        'en' => [
            'summary' => 'Treatment booking slot clash prevention and loyalty tiers admin UI refresh.',
            'changes' => [
                'Block double-booking the same beautician, date, and time using orders and treatment bookings with a checkout lock.',
                'Redesign membership tiers admin page: distribution chart, full-width ladder, search table, and BM translations.',
            ],
        ],
        'ms' => [
            'summary' => 'Pencegahan pertembungan slot tempahan rawatan dan UI admin peringkat keahlian baharu.',
            'changes' => [
                'Sekat tempahan berganda beautician/tarikh/masa melalui pesanan dan tempahan dengan kunci semasa checkout.',
                'Reka semula halaman admin peringkat keahlian: carta taburan, tangga lebar penuh, jadual carian, dan terjemahan BM.',
            ],
        ],
    ],
    '4.7.43' => [
        'en' => [
            'summary' => 'Fix cart page JS error when the cart is empty.',
            'changes' => [
                'Guard hideSkeleton on cart and compare pages when the skeleton element is not rendered.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki ralat JS halaman cart apabila troli kosong.',
            'changes' => [
                'Lindungi hideSkeleton pada halaman cart dan compare apabila elemen skeleton tidak dipaparkan.',
            ],
        ],
    ],
    '4.7.42' => [
        'en' => [
            'summary' => 'Tighter admin desktop density at 100% zoom.',
            'changes' => [
                'Add desktop-density styles for smaller typography, forms, sidebar, and tables.',
                'Compact spa branch and user profile layouts so content fits desktop screens.',
            ],
        ],
        'ms' => [
            'summary' => 'Ketumpatan desktop admin lebih padat pada zoom 100%.',
            'changes' => [
                'Tambah gaya desktop-density untuk tipografi, borang, sidebar, dan jadual yang lebih kecil.',
                'Padatkan susun atur cawangan spa dan profil pengguna supaya muat pada skrin desktop.',
            ],
        ],
    ],
    '4.7.41' => [
        'en' => [
            'summary' => 'Add spa branch and beautician demo seeders for local restore.',
            'changes' => [
                'Add SpaBranchDatabaseSeeder with IMMA Seri Laris Kajang branch and beautician links.',
                'Run beautician and spa branch seeders during demo data restore.',
            ],
        ],
        'ms' => [
            'summary' => 'Tambah seeder demo cawangan spa dan beautician untuk pemulihan tempatan.',
            'changes' => [
                'Tambah SpaBranchDatabaseSeeder dengan cawangan IMMA Seri Laris Kajang dan pautan beautician.',
                'Jalankan seeder beautician dan cawangan spa semasa pemulihan data demo.',
            ],
        ],
    ],
    '4.7.40' => [
        'en' => [
            'summary' => 'Fix admin users list status, profile hero avatar, and access layout.',
            'changes' => [
                'Show activation status correctly on the users index (Sentinel, not is_active).',
                'Fix profile hero avatar photo display and live sync when uploading a photo.',
                'Lay out Roles and Activated side by side on the user edit account tab.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki status senarai pengguna admin, avatar hero profil, dan susun atur akses.',
            'changes' => [
                'Papar status pengaktifan dengan betul pada indeks pengguna (Sentinel, bukan is_active).',
                'Baiki paparan foto avatar hero profil dan sync langsung semasa muat naik foto.',
                'Susun Peranan dan Diaktifkan sebelah menyebelah pada tab akaun edit pengguna.',
            ],
        ],
    ],
    '4.7.39' => [
        'en' => [
            'summary' => 'Restore hidden admin menus (Beauticians, Spa Branches, and other new modules).',
            'changes' => [
                'Add admin:sync-module-permissions to merge all module permissions into the Admin role.',
                'Run permission sync during settings:restore-imma and demo data restore.',
                'Add beautician:grant-admin-permissions; fix grant commands to target the Admin role by name.',
            ],
        ],
        'ms' => [
            'summary' => 'Pulih menu admin tersembunyi (Beautician, Cawangan Spa, dan modul baharu lain).',
            'changes' => [
                'Tambah admin:sync-module-permissions untuk gabung semua kebenaran modul ke role Admin.',
                'Jalankan sync kebenaran semasa settings:restore-imma dan pemulihan data demo.',
                'Tambah beautician:grant-admin-permissions; baiki arahan grant supaya sasarkan role Admin mengikut nama.',
            ],
        ],
    ],
    '4.7.38' => [
        'en' => [
            'summary' => 'Checkout rewards section: star icon and light yellow highlight.',
            'changes' => [
                'Add reward points SVG icon beside the checkout loyalty label.',
                'Style the loyalty redeem block with a light yellow background and amber border.',
            ],
        ],
        'ms' => [
            'summary' => 'Bahagian ganjaran checkout: ikon bintang dan latar kuning lembut.',
            'changes' => [
                'Tambah ikon SVG mata ganjaran di sebelah label loyalty pada checkout.',
                'Gaya blok tebus mata dengan latar kuning lembut dan sempadan amber.',
            ],
        ],
    ],
    '4.7.37' => [
        'en' => [
            'summary' => 'Checkout and account UX: billing autofill, loyalty card on profile, CHIP settings restore.',
            'changes' => [
                'Auto-fill checkout billing from saved address, last order, or profile when logged in.',
                'Auto-fill first and last name on My Addresses; fix Alpine x-data JSON on that page.',
                'Show loyalty membership card and reward points in the profile sidebar.',
                'Restore missing CHIP payment settings via seeder and settings:restore-imma.',
            ],
        ],
        'ms' => [
            'summary' => 'UX checkout & akaun: autofill bil, kad loyalty pada profil, pulih tetapan CHIP.',
            'changes' => [
                'Autofill bil checkout dari alamat simpanan, pesanan terakhir, atau profil apabila log masuk.',
                'Autofill nama pertama & akhir pada Alamat Saya; baiki JSON x-data Alpine pada halaman itu.',
                'Paparkan kad keahlian loyalty dan mata ganjaran di sidebar profil.',
                'Pulih tetapan pembayaran CHIP yang hilang melalui seeder dan settings:restore-imma.',
            ],
        ],
    ],
    '4.7.36' => [
        'en' => [
            'summary' => 'Fix false "Unsaved changes" badge when switching settings tabs.',
            'changes' => [
                'Compare form snapshot to a post-init baseline instead of flagging the first programmatic change.',
                'Re-baseline after store country state options load from the API.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki lencana "Perubahan belum disimpan" palsu semasa tukar tab tetapan.',
            'changes' => [
                'Bandingkan snapshot borang dengan baseline selepas init, bukan tandakan perubahan programatik pertama.',
                'Tetapkan semula baseline selepas pilihan negeri kedai dimuatkan dari API.',
            ],
        ],
    ],
    '4.7.35' => [
        'en' => [
            'summary' => 'Fix 500 when using Check on GitHub or Update from GitHub in Settings.',
            'changes' => [
                'Pass ReleaseNotesService into the app version action handler (was missing and caused ArgumentCountError).',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki 500 semasa guna Semak di GitHub atau Kemas kini dari GitHub dalam Tetapan.',
            'changes' => [
                'Hantar ReleaseNotesService ke handler tindakan versi app (tiada sebelum ini dan menyebabkan ArgumentCountError).',
            ],
        ],
    ],
    '4.7.34' => [
        'en' => [
            'summary' => 'Fix admin Settings page 500 errors on production shared hosting.',
            'changes' => [
                'Harden System tab boot when release notes, catalog sync, or session data is unavailable.',
                'Guard @hasAccess Blade directives when the admin user context is missing.',
                'Skip catalog-sync route links when routes are not registered after a partial deploy.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki ralat 500 halaman Tetapan admin pada production shared hosting.',
            'changes' => [
                'Kukuhkan tab System apabila nota release, catalog sync, atau data sesi tidak tersedia.',
                'Lindungi arahan Blade @hasAccess apabila konteks pengguna admin tiada.',
                'Langkau pautan route catalog-sync jika route belum didaftarkan selepas deploy separa.',
            ],
        ],
    ],
    '4.7.33' => [
        'en' => [
            'summary' => 'Stop browser autofill from injecting admin login email into settings fields.',
            'changes' => [
                'Move settings sidebar search outside the save form and block password-manager autofill on unrelated inputs.',
            ],
        ],
        'ms' => [
            'summary' => 'Hentikan autofill pelayar daripada memasukkan e-mel log masuk admin ke medan tetapan.',
            'changes' => [
                'Alihkan carian sidebar tetapan keluar dari borang simpan dan sekat autofill pengurus kata laluan pada input yang tidak berkaitan.',
            ],
        ],
    ],
    '4.7.32' => [
        'en' => [
            'summary' => 'Fix duplicate error when restoring homepage blog section settings.',
            'changes' => [
                'Blog section seeder uses updateOrInsert for translations so Restore blog posts can run repeatedly.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki ralat duplicate semasa restore tetapan seksyen blog laman utama.',
            'changes' => [
                'Seeder seksyen blog guna updateOrInsert untuk terjemahan supaya Restore blog posts boleh dijalankan berulang kali.',
            ],
        ],
    ],
    '4.7.31' => [
        'en' => [
            'summary' => 'Fix Restore blog posts button in admin System settings.',
            'changes' => [
                'Register Blog Artisan commands for web requests so Restore blog posts works from the admin panel.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki butang Restore blog posts dalam tetapan System admin.',
            'changes' => [
                'Daftarkan perintah Artisan Blog untuk permintaan web supaya Restore blog posts berfungsi dari panel admin.',
            ],
        ],
    ],
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
