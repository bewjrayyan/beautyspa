<?php

/**
 * Release changelog shown in Admin → Settings → System after each version update.
 * Add a new entry whenever app/AestheticCart.php VERSION is bumped.
 */
return [
    '4.8.5' => [
        'en' => [
            'summary' => 'Add icons to mobile footer accordion section titles.',
            'changes' => [
                'Show a themed icon beside each Help & Support footer menu title (Contact, Account, Links, Information, Tags).',
            ],
        ],
        'ms' => [
            'summary' => 'Tambah ikon pada tajuk seksyen accordion footer mudah alih.',
            'changes' => [
                'Paparkan ikon bertema di sebelah setiap tajuk menu footer Help & Support (Hubungi, Akaun, Pautan, Maklumat, Tag).',
            ],
        ],
    ],
    '4.8.4' => [
        'en' => [
            'summary' => 'Center product slider pagination dots on mobile.',
            'changes' => [
                'Keep swiper dots centered between the prev and next arrows on mobile sliders.',
                'Disable dynamic bullet shifting that pushed pagination outside the control bar.',
            ],
        ],
        'ms' => [
            'summary' => 'Centerkan titik pagination slider produk pada mudah alih.',
            'changes' => [
                'Kekalkan titik swiper di tengah antara anak panah prev dan next pada slider mudah alih.',
                'Matikan peralihan dynamic bullet yang menolak pagination keluar dari bar kawalan.',
            ],
        ],
    ],
    '4.8.3' => [
        'en' => [
            'summary' => 'Narrower featured category cards and centered mobile footer nav.',
            'changes' => [
                'Show slightly narrower product cards in the Shop by Category slider with more next-item preview.',
                'Balance bottom navigation spacing and nudge the Home icon left for better visual centering.',
            ],
        ],
        'ms' => [
            'summary' => 'Kad kategori pilihan lebih kecil dan nav footer mudah alih lebih center.',
            'changes' => [
                'Paparkan kad produk sedikit lebih kecil dalam slider Shop by Category dengan lebih banyak pratonton item seterusnya.',
                'Seimbangkan jarak navigasi bawah dan geser ikon Home ke kiri untuk penjajaran visual yang lebih baik.',
            ],
        ],
    ],
    '4.8.2' => [
        'en' => [
            'summary' => 'Fix featured category product images not filling the card.',
            'changes' => [
                'Use square aspect ratio with object-fit cover so product photos fill the image area edge to edge.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki imej produk kategori pilihan yang tidak memenuhi kad.',
            'changes' => [
                'Guna nisbah segi empat dengan object-fit cover supaya foto produk memenuhi kawasan imej sepenuhnya.',
            ],
        ],
    ],
    '4.8.1' => [
        'en' => [
            'summary' => 'Ship rebuilt frontend assets for shared-hosting deploy.',
            'changes' => [
                'Commit updated public/build/ so GitHub deploy and admin version update include CSS/JS changes without npm on the server.',
                'Add scripts/ship-release-assets.sh — run before each release commit when storefront assets changed.',
            ],
        ],
        'ms' => [
            'summary' => 'Sertakan aset frontend dibina semula untuk deploy shared hosting.',
            'changes' => [
                'Commit public/build/ dikemas kini supaya deploy GitHub dan kemas kini versi admin menyertakan perubahan CSS/JS tanpa npm di pelayan.',
                'Tambah scripts/ship-release-assets.sh — jalankan sebelum setiap commit release apabila aset storefront berubah.',
            ],
        ],
    ],
    '4.8.0' => [
        'en' => [
            'summary' => 'Homepage product tabs View All link and mobile slider polish.',
            'changes' => [
                'Add a View All link on the right of homepage product tab headers linking to the shop listing.',
                'Match featured category mobile slider peek to the blog carousel with shorter product images.',
                'Restore mobile homepage search without sticky positioning.',
            ],
        ],
        'ms' => [
            'summary' => 'Pautan Lihat Semua tab produk laman utama dan penambahbaikan slider mudah alih.',
            'changes' => [
                'Tambah pautan Lihat Semua di kanan header tab produk laman utama ke senarai kedai.',
                'Selaraskan peek slider kategori pilihan mudah alih dengan carousel blog menggunakan imej produk lebih pendek.',
                'Pulihkan carian laman utama mudah alih tanpa kedudukan sticky.',
            ],
        ],
    ],
    '4.7.100' => [
        'en' => [
            'summary' => 'Match featured category product slider peek to the blog carousel.',
            'changes' => [
                'Use the same mobile swiper settings as the blog carousel for consistent next-slide previews.',
                'Show product images in the peek with a shorter image ratio like blog cards.',
            ],
        ],
        'ms' => [
            'summary' => 'Selaraskan slider produk kategori pilihan dengan carousel blog.',
            'changes' => [
                'Guna tetapan swiper mudah alih yang sama seperti carousel blog untuk pratonton slide seterusnya yang konsisten.',
                'Paparkan imej produk dalam peek dengan nisbah imej lebih pendek seperti kad blog.',
            ],
        ],
    ],
    '4.7.99' => [
        'en' => [
            'summary' => 'Improve featured category product slider peek on mobile.',
            'changes' => [
                'Show more of the next product card in the Shop by Category slider on mobile.',
                'Stop clipping the slider at the card edge so the next item stays visible.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki paparan slider produk kategori pilihan pada mudah alih.',
            'changes' => [
                'Paparkan lebih banyak kad produk seterusnya dalam slider Shop by Category pada mudah alih.',
                'Elak potongan slider di tepi kad supaya item seterusnya kekal kelihatan.',
            ],
        ],
    ],
    '4.7.98' => [
        'en' => [
            'summary' => 'Restore mobile search bar without sticky positioning.',
            'changes' => [
                'Bring back the homepage and blog search field on mobile after removing the sticky version.',
                'Search now scrolls with the page instead of staying fixed below the header.',
            ],
        ],
        'ms' => [
            'summary' => 'Pulihkan bar carian mudah alih tanpa kedudukan sticky.',
            'changes' => [
                'Kembalikan medan carian laman utama dan blog pada mudah alih selepas buang versi sticky.',
                'Carian kini ikut skrol halaman dan tidak kekal tetap di bawah header.',
            ],
        ],
    ],
    '4.7.97' => [
        'en' => [
            'summary' => 'Remove sticky mobile search bar from homepage and blog index.',
            'changes' => [
                'Remove the fixed search area below the header on mobile homepage and blog listing.',
                'Restore normal content spacing now that the sticky search offset is no longer needed.',
            ],
        ],
        'ms' => [
            'summary' => 'Buang bar carian sticky mudah alih dari laman utama dan indeks blog.',
            'changes' => [
                'Buang kawasan carian tetap di bawah header pada laman utama mudah alih dan senarai blog.',
                'Pulihkan jarak kandungan biasa selepas offset carian sticky dibuang.',
            ],
        ],
    ],
    '4.7.96' => [
        'en' => [
            'summary' => 'Auto-convert uploaded favicon PNG to ICO for stable browser tabs.',
            'changes' => [
                'Generate a real favicon.ico from the uploaded PNG/JPEG/WebP image (16, 32, and 48 px).',
                'Serve the ICO binary directly instead of redirecting to the PNG file.',
                'Keep the original image for Apple touch icons while tabs use the converted ICO.',
            ],
        ],
        'ms' => [
            'summary' => 'Tukar automatik favicon PNG yang dimuat naik kepada ICO untuk tab pelayar yang stabil.',
            'changes' => [
                'Jana favicon.ico sebenar daripada imej PNG/JPEG/WebP yang dimuat naik (16, 32, dan 48 px).',
                'Sediakan fail ICO secara langsung dan bukannya redirect ke fail PNG.',
                'Kekalkan imej asal untuk ikon Apple touch manakala tab pelayar guna ICO yang ditukar.',
            ],
        ],
    ],
    '4.7.95' => [
        'en' => [
            'summary' => 'Fix browser tab favicon on subdirectory installs.',
            'changes' => [
                'Serve /favicon.ico from the storefront favicon setting so Chrome finds the icon automatically.',
                'Move favicon link tags to the top of the page head for faster browser discovery.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki favicon tab pelayar pada pemasangan subdirectory.',
            'changes' => [
                'Sediakan /favicon.ico daripada tetapan favicon storefront supaya Chrome menemui ikon secara automatik.',
                'Alihkan tag pautan favicon ke bahagian atas head halaman untuk penemuan pelayar yang lebih pantas.',
            ],
        ],
    ],
    '4.7.94' => [
        'en' => [
            'summary' => 'Fix favicon and WhatsApp share preview image URLs.',
            'changes' => [
                'Show the storefront favicon in admin and storefront browser tabs with the correct image MIME type.',
                'Fix duplicated subdirectory in Open Graph and media absolute URLs for subdirectory installs.',
                'Fall back to the favicon for social share previews when needed.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki favicon dan URL imej pratonton kongsi WhatsApp.',
            'changes' => [
                'Paparkan favicon storefront pada tab pelayar admin dan kedai dengan jenis MIME imej yang betul.',
                'Baiki subdirectory berganda pada URL Open Graph dan media mutlak untuk pemasangan subdirectory.',
                'Guna favicon sebagai sandaran untuk pratonton kongsi sosial apabila perlu.',
            ],
        ],
    ],
    '4.7.93' => [
        'en' => [
            'summary' => 'Mobile promo video sound toggle on the homepage.',
            'changes' => [
                'Add a tap-to-unmute sound button on mobile home promo videos while keeping autoplay muted by default.',
                'Improve promo video markup so the sound control works alongside optional promo links.',
            ],
        ],
        'ms' => [
            'summary' => 'Butang bunyi untuk video promo mudah alih pada laman utama.',
            'changes' => [
                'Tambah butang tap untuk hidupkan bunyi pada video promo mudah alih sambil kekalkan autoplay tanpa bunyi secara lalai.',
                'Baiki markup video promo supaya kawalan bunyi berfungsi bersama pautan promo pilihan.',
            ],
        ],
    ],
    '4.7.92' => [
        'en' => [
            'summary' => 'Mobile home promo section, Birthday Founder Mega Sale catalog import, and larger video uploads.',
            'changes' => [
                'Add admin-controlled mobile-only home promo (poster image or video) above product tabs on the storefront.',
                'Add imma:import-birthday-founder-mega-sale command and flyer catalog for seven promo treatment products.',
                'Raise media upload limit to 40 MB and support MOV for promo videos.',
                'Show mobile promo video at its natural aspect ratio instead of a fixed crop height.',
            ],
        ],
        'ms' => [
            'summary' => 'Seksyen promo laman utama mudah alih, import katalog Birthday Founder Mega Sale, dan muat naik video lebih besar.',
            'changes' => [
                'Tambah promo laman utama khusus mudah alih (poster imej atau video) di atas tab produk, boleh dikawal dari Admin → Storefront.',
                'Tambah arahan imma:import-birthday-founder-mega-sale dan katalog flyer untuk tujuh produk rawatan promo.',
                'Naikkan had muat naik media ke 40 MB dan sokong MOV untuk video promo.',
                'Paparkan video promo mudah alih mengikut nisbah aspek asal video, bukan ketinggian tetap.',
            ],
        ],
    ],
    '4.7.91' => [
        'en' => [
            'summary' => 'Add artisan command to refresh WhatsApp message templates on production.',
            'changes' => [
                'New setting:refresh-whatsapp-templates command with --order-only and --force options.',
                'Safely re-apply WhatsApp template defaults without fragile tinker one-liners on shared hosting.',
            ],
        ],
        'ms' => [
            'summary' => 'Tambah arahan artisan untuk refresh templat mesej WhatsApp pada production.',
            'changes' => [
                'Arahan baharu setting:refresh-whatsapp-templates dengan pilihan --order-only dan --force.',
                'Guna semula templat lalai WhatsApp dengan selamat tanpa one-liner tinker yang rapuh pada shared hosting.',
            ],
        ],
    ],
    '4.7.90' => [
        'en' => [
            'summary' => 'Configurable WhatsApp message templates and full order pricing in automated messages.',
            'changes' => [
                'Add per-notification message templates in Admin → Settings → WhatsApp (welcome, reminders, new order, completed order, beautician, and more).',
                'Share OrderPricingBreakdown across invoice views and WhatsApp PAYMENT SUMMARY (tax, shipping, coupon, loyalty, processing fee).',
                'Enrich new-order admin/customer and completed-order group/beautician WhatsApp with items, appointment details, and full payment summary.',
                'Introduce WhatsAppMessageTemplate and OrderWhatsAppMessageBuilder for consistent placeholder replacement.',
                'Improve WhatsApp settings tab spacing and textarea layout for long templates.',
            ],
        ],
        'ms' => [
            'summary' => 'Templat mesej WhatsApp boleh ubah dan harga pesanan penuh dalam mesej automatik.',
            'changes' => [
                'Tambah templat mesej setiap notifikasi di Admin → Tetapan → WhatsApp (alu-aluan, peringatan, pesanan baharu, pesanan selesai, beautician, dan lain-lain).',
                'Kongsi OrderPricingBreakdown antara paparan invois dan PAYMENT SUMMARY WhatsApp (cukai, penghantaran, kupon, kesetiaan, yuran pemprosesan).',
                'Perkaya WhatsApp pesanan baharu (admin/pelanggan) dan pesanan selesai (kumpulan/beautician) dengan item, temujanji, dan ringkasan bayaran penuh.',
                'Perkenalkan WhatsAppMessageTemplate dan OrderWhatsAppMessageBuilder untuk penggantian placeholder yang konsisten.',
                'Baiki jarak tab tetapan WhatsApp dan susun textarea untuk templat panjang.',
            ],
        ],
    ],
    '4.7.89' => [
        'en' => [
            'summary' => 'Show full pricing breakdown on account invoice and receipt pages.',
            'changes' => [
                'Add tax, coupon, loyalty redemption, payment processing fee, and shipping lines to account invoice and receipt.',
                'Align invoice and receipt totals with the account order detail summary.',
            ],
        ],
        'ms' => [
            'summary' => 'Papar breakdown harga penuh pada halaman invois dan resit akaun.',
            'changes' => [
                'Tambah baris cukai, kupon, penebusan kesetiaan, yuran pemprosesan bayaran, dan penghantaran pada invois dan resit akaun.',
                'Selaraskan jumlah invois dan resit dengan ringkasan butiran pesanan akaun.',
            ],
        ],
    ],
    '4.7.88' => [
        'en' => [
            'summary' => 'Fix account invoice tab closing on mobile and validate Vite assets on deploy.',
            'changes' => [
                'Stop auto-print on customer invoice and receipt pages so new tabs stay open on mobile.',
                'Add a Print or save as PDF button on account and checkout invoice views.',
                'Use direct links instead of window.open for mobile order invoice and receipt actions.',
                'Extend verify-production-deploy.php to check all files listed in public/build/manifest.json.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki tab invois akaun yang tertutup pada mobile dan sahkan aset Vite semasa deploy.',
            'changes' => [
                'Hentikan auto-print pada halaman invois dan resit pelanggan supaya tab baharu kekal terbuka pada mobile.',
                'Tambah butang Cetak atau simpan sebagai PDF pada paparan invois akaun dan checkout.',
                'Guna pautan terus dan bukannya window.open untuk tindakan invois dan resit pesanan pada mobile.',
                'Lanjutkan verify-production-deploy.php untuk semak semua fail dalam public/build/manifest.json.',
            ],
        ],
    ],
    '4.7.87' => [
        'en' => [
            'summary' => 'Add missing admin release notes for v4.7.85 and v4.7.86.',
            'changes' => [
                'Show installed-version changelog on Admin → Settings → System for recent releases.',
            ],
        ],
        'ms' => [
            'summary' => 'Tambah nota keluaran admin yang hilang untuk v4.7.85 dan v4.7.86.',
            'changes' => [
                'Papar changelog versi dipasang pada Admin → Tetapan → Sistem untuk keluaran terkini.',
            ],
        ],
    ],
    '4.7.86' => [
        'en' => [
            'summary' => 'Fix CHIP processing fees, expand checkout order summary, and send WhatsApp immediately on checkout.',
            'changes' => [
                'FPX surcharge uses flat sen (default 100 = RM1.00); card and Atome use CHIP percentage fees (2% / 5.3%).',
                'Persist payment processing fee in order total so account and CHIP checkout totals match.',
                'Show full order breakdown on checkout complete (subtotal, tax, coupon, loyalty, fee, payment status).',
                'Send new-order and completed-order WhatsApp without outbound queue delay (immediate delivery).',
                'Clarify CHIP surcharge fields in admin settings (sen for FPX, percent for cards and Atome).',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki yuran CHIP, kembangkan ringkasan checkout, dan hantar WhatsApp segera selepas checkout.',
            'changes' => [
                'Caj FPX menggunakan sen tetap (lalai 100 = RM1.00); kad dan Atome guna peratus yuran CHIP (2% / 5.3%).',
                'Simpan yuran pemprosesan dalam jumlah pesanan supaya akaun dan jumlah CHIP sepadan.',
                'Papar breakdown pesanan penuh pada halaman checkout selesai (subtotal, cukai, kupon, kesetiaan, yuran, status bayaran).',
                'Hantar WhatsApp pesanan baharu/selesai tanpa delay queue keluar (penghantaran segera).',
                'Jelaskan medan caj CHIP dalam tetapan admin (sen untuk FPX, peratus untuk kad dan Atome).',
            ],
        ],
    ],
    '4.7.85' => [
        'en' => [
            'summary' => 'Fire OrderStatusChanged on checkout completion so WhatsApp and loyalty run automatically.',
            'changes' => [
                'Dispatch OrderStatusChanged when paid checkout marks the order as completed.',
                'Enable completed-order WhatsApp to group and beautician without manual admin status change.',
                'Trigger loyalty earn and treatment booking sync on checkout completion.',
            ],
        ],
        'ms' => [
            'summary' => 'Picu OrderStatusChanged pada checkout selesai supaya WhatsApp dan kesetiaan jalan automatik.',
            'changes' => [
                'Hantar OrderStatusChanged bila checkout berbayar menandakan pesanan sebagai selesai.',
                'Benarkan WhatsApp pesanan selesai ke kumpulan dan beautician tanpa ubah status manual di admin.',
                'Picu mata kesetiaan dan penyegerakan tempahan rawatan pada checkout selesai.',
            ],
        ],
    ],
    '4.7.84' => [
        'en' => [
            'summary' => 'Account order detail pricing breakdown and admin permission labels.',
            'changes' => [
                'Show variant and option prices inline on mobile and desktop order item cards.',
                'Expand order summary with tax, shipping, coupon, loyalty, and payment processing fee lines.',
                'Hide shipping address on account orders when all products are virtual or treatment.',
                'Fix raw admin role permission labels with permission_label helper and missing group keys.',
            ],
        ],
        'ms' => [
            'summary' => 'Breakdown harga pesanan akaun dan label kebenaran admin.',
            'changes' => [
                'Papar harga variant dan pilihan sebaris pada kad item pesanan mobile dan desktop.',
                'Kembangkan ringkasan pesanan dengan cukai, penghantaran, kupon, kesetiaan, dan yuran pemprosesan.',
                'Sembunyikan alamat penghantaran bila semua produk adalah virtual atau rawatan.',
                'Baiki label kebenaran peranan admin mentah dengan helper permission_label dan kunci kumpulan baharu.',
            ],
        ],
    ],
    '4.7.83' => [
        'en' => [
            'summary' => 'Show product consultation button on mobile below variant picker.',
            'changes' => [
                'Add inline Get Free Consultations CTA below product options when sidebar is hidden.',
                'Reuse shared consultation_cta partial for desktop sidebar and mobile layout.',
            ],
        ],
        'ms' => [
            'summary' => 'Papar butang konsultasi produk pada mobile di bawah pemilih variant.',
            'changes' => [
                'Tambah CTA Dapatkan Konsultasi Percuma di bawah pilihan produk bila sidebar disembunyikan.',
                'Guna semula partial consultation_cta untuk sidebar desktop dan layout mobile.',
            ],
        ],
    ],
    '4.7.82' => [
        'en' => [
            'summary' => 'Fix mobile sidebar menu close button on outer curve.',
            'changes' => [
                'Move the menu drawer X button outside the panel edge on mobile.',
                'Align close button with the curved header using the desktop offset.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki butang tutup menu sidebar pada lengkungan luar mobile.',
            'changes' => [
                'Alihkan butang X drawer menu ke luar tepi panel pada mobile.',
                'Selaraskan butang tutup dengan header melengkung menggunakan offset desktop.',
            ],
        ],
    ],
    '4.7.81' => [
        'en' => [
            'summary' => 'Mobile cart icon, drawer fixes, and product dock variant UX.',
            'changes' => [
                'Show trolley icon in mobile header and open sidebar cart from bottom nav.',
                'Fix mobile menu/cart drawer slide direction, z-index, and overlay sync.',
                'Show MYR 0.00 in product mobile dock until a variant is selected.',
                'Prompt users to choose an option before add to cart with localized toast.',
                'Add storefront_js_trans helper so new JS strings resolve before cache sync.',
            ],
        ],
        'ms' => [
            'summary' => 'Ikon troli mobile, baiki drawer, dan UX variant pada dock produk.',
            'changes' => [
                'Papar ikon troli pada header mobile dan buka troli sidebar dari nav bawah.',
                'Baiki arah slide drawer menu/troli, z-index, dan penyegerakan overlay.',
                'Papar MYR 0.00 pada dock produk mobile sehingga variant dipilih.',
                'Ingatkan pengguna pilih pilihan sebelum tambah troli dengan toast berterjemah.',
                'Tambah helper storefront_js_trans supaya string JS baharu resolve sebelum cache sync.',
            ],
        ],
    ],
    '4.7.80' => [
        'en' => [
            'summary' => 'Disable pinch zoom and iOS input auto-zoom on mobile storefront.',
            'changes' => [
                'Lock viewport scale on public layout for app-like mobile browsing.',
                'Set 16px form control font size on mobile to prevent iOS focus zoom.',
                'Add touch-action manipulation to reduce double-tap zoom on mobile.',
            ],
        ],
        'ms' => [
            'summary' => 'Lumpuhkan pinch zoom dan auto-zoom input iOS pada storefront mobile.',
            'changes' => [
                'Kunci skala viewport pada layout awam untuk pelayaran mobile gaya app.',
                'Set saiz fon 16px pada kawalan borang mobile untuk elak zoom fokus iOS.',
                'Tambah touch-action manipulation untuk kurangkan double-tap zoom pada mobile.',
            ],
        ],
    ],
    '4.7.79' => [
        'en' => [
            'summary' => 'Profile hero mobile redesign and Language & Currency sheet z-index fix.',
            'changes' => [
                'Rebuild mobile profile hero with clean avatar + name row, photo actions, and full-width stats.',
                'Fix Language & Currency bottom sheet stacking above mobile bottom navigation.',
            ],
        ],
        'ms' => [
            'summary' => 'Reka semula hero profil mobile dan baiki z-index sheet Bahasa & Mata Wang.',
            'changes' => [
                'Bina semula hero profil mobile dengan baris avatar + nama, tindakan foto, dan stat lebar penuh.',
                'Baiki susunan sheet Bahasa & Mata Wang di atas navigasi bawah mobile.',
            ],
        ],
    ],
    '4.7.78' => [
        'en' => [
            'summary' => 'Mobile account UX polish, homepage search, and layout alignment fixes.',
            'changes' => [
                'Align account subpage cards with footer width using full-width panel-wrap gutter.',
                'Redesign mobile profile hero with gradient header, centered avatar, and stat tiles.',
                'Fix password field borders on mobile profile security section.',
                'Add sticky product search on homepage and shared sticky search partial for blog.',
                'Improve features carousel peek, pastel card tints, and bottom navigation centering.',
            ],
        ],
        'ms' => [
            'summary' => 'Penambahbaikan UX akaun mobile, carian laman utama, dan penjajaran layout.',
            'changes' => [
                'Jajarkan kad subhalaman akaun dengan lebar footer menggunakan gutter panel-wrap penuh.',
                'Reka semula hero profil mobile dengan header gradien, avatar berpusat, dan jubin stat.',
                'Baiki border field kata laluan pada bahagian keselamatan profil mobile.',
                'Tambah carian produk sticky pada laman utama dan partial carian sticky dikongsi untuk blog.',
                'Tambah baik peek karusel ciri, warna kad pastel, dan pemusatan navigasi bawah.',
            ],
        ],
    ],
    '4.7.77' => [
        'en' => [
            'summary' => 'Highlighted fixed blog search bar on mobile index.',
            'changes' => [
                'Add app-style search card with icon field and storefront-aware search URL.',
                'Pin blog search below the sticky header on mobile index so it stays visible while scrolling.',
                'Move search out of the flex column layout and add content offset padding.',
            ],
        ],
        'ms' => [
            'summary' => 'Bar carian blog mobile yang menonjol dan kekal di indeks.',
            'changes' => [
                'Tambah kad carian gaya app dengan ikon dalam field dan URL carian yang peka subdirectory.',
                'Pin carian blog di bawah header sticky pada indeks mobile supaya kekal kelihatan semasa scroll.',
                'Alih carian keluar dari layout flex column dan tambah padding offset kandungan.',
            ],
        ],
    ],
    '4.7.76' => [
        'en' => [
            'summary' => 'Mobile app-style category chips on blog index and sidebar.',
            'changes' => [
                'Replace long vertical category list with horizontal scroll pill chips on mobile.',
                'Add View All chip, category count badge, scroll snap, and fade edge hint.',
                'Share sidebar mobile styles between blog index and blog post pages.',
            ],
        ],
        'ms' => [
            'summary' => 'Chip kategori gaya app mobile pada indeks blog dan sidebar.',
            'changes' => [
                'Ganti senarai kategori menegak panjang dengan pill chip scroll mendatar pada mobile.',
                'Tambah chip Lihat Semua, badge bilangan kategori, scroll snap, dan petunjuk fade tepi.',
                'Kongsi gaya sidebar mobile antara halaman indeks blog dan catatan blog.',
            ],
        ],
    ],
    '4.7.75' => [
        'en' => [
            'summary' => 'Fix blog pagination links pointing to localhost on production.',
            'changes' => [
                'Build paginator hrefs with the real request origin (domain + scheme) instead of defaulting to localhost.',
                'Prefer SCRIPT_NAME base path (/v2) over locale segment heuristics on rewritten URLs.',
                'Derive pagination path from the current request URL when on the same blog route.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki pautan pagination blog yang pergi ke localhost pada production.',
            'changes' => [
                'Bina href paginator dengan origin request sebenar (domain + scheme) bukan default localhost.',
                'Utamakan base path SCRIPT_NAME (/v2) berbanding heuristik segmen locale pada URL yang ditulis semula.',
                'Ambil path pagination daripada URL request semasa apabila pada route blog yang sama.',
            ],
        ],
    ],
    '4.7.74' => [
        'en' => [
            'summary' => 'Fix blog pagination 404 on alternate subdirectory installs (e.g. /v2).',
            'changes' => [
                'Detect install base path from locale-prefixed URLs (/v2/en/...) instead of stale APP_URL only.',
                'Set blog listing pagination paths from named storefront routes with correct subdirectory prefix.',
                'Add aestheticcart_pagination_url helper for consistent paginator href normalization.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki pagination blog 404 pada pemasangan subdirectory alternatif (cth. /v2).',
            'changes' => [
                'Kesan base path pemasangan daripada URL berawalan locale (/v2/en/...) bukan APP_URL lama sahaja.',
                'Set path pagination senarai blog daripada named route storefront dengan awalan subdirectory betul.',
                'Tambah helper aestheticcart_pagination_url untuk normalisasi href paginator yang konsisten.',
            ],
        ],
    ],
    '4.7.73' => [
        'en' => [
            'summary' => 'Fix homepage hero slider crash when slider element or data is missing.',
            'changes' => [
                'Guard initHeroSlider when .home-slider is absent (e.g. mobile preview) instead of destructuring undefined data.',
                'Scope slider lookup to the Hero Alpine component and pass the DOM element directly to Swiper.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki crash slider hero laman utama apabila elemen atau data slider tiada.',
            'changes' => [
                'Lindungi initHeroSlider apabila .home-slider tiada (cth. pratonton mobile) supaya tidak destruct undefined data.',
                'Hadkan carian slider dalam komponen Hero Alpine dan hantar elemen DOM terus ke Swiper.',
            ],
        ],
    ],
    '4.7.72' => [
        'en' => [
            'summary' => 'Admin order stamp cards, mobile blog UX, and subdirectory pagination fix.',
            'changes' => [
                'Add stamp card section on admin order detail with customer-style card UI and order-specific status.',
                'Redesign blog post and listing pages for mobile app layout (hero cards, category chips, full-width images).',
                'Fix pagination links on subdirectory installs (e.g. /fleetcart) so page 2 no longer 404s.',
            ],
        ],
        'ms' => [
            'summary' => 'Kad cop order admin, UX blog mobile, dan baiki pagination subdirectory.',
            'changes' => [
                'Tambah section kad cop pada detail order admin dengan UI kad gaya pelanggan dan status order.',
                'Reka semula halaman blog dan senarai blog untuk layout mobile app (kad hero, chip kategori, imej penuh).',
                'Baiki pautan pagination pada pemasangan subdirectory (cth. /fleetcart) supaya page 2 tidak 404.',
            ],
        ],
    ],
    '4.7.71' => [
        'en' => [
            'summary' => 'Fix admin table bulk select checkboxes navigating away instead of multi-select delete.',
            'changes' => [
                'Stop row navigation when clicking bulk-select checkboxes or action columns in DataTables.',
                'Use delegated namespaced checkbox events to prevent duplicate listeners on table redraw.',
                'Adjust users table and datatables styles so only navigable cells show a pointer cursor.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki checkbox bulk select admin table yang navigasi keluar instead of multi-select delete.',
            'changes' => [
                'Hentikan navigasi row apabila klik checkbox bulk select atau lajur tindakan dalam DataTables.',
                'Guna event checkbox namespaced supaya listener tidak duplicate semasa table redraw.',
                'Laraskan gaya users table dan datatables — cursor pointer hanya pada sel navigasi.',
            ],
        ],
    ],
    '4.7.70' => [
        'en' => [
            'summary' => 'Fix WhatsApp product share link previews missing OG image when custom message omits the product URL.',
            'changes' => [
                'Always append the product URL to WhatsApp share text when a custom template does not include {product_url}.',
                'Improve product Open Graph: optional SEO og image, HTTPS image URLs, and og:image:type for crawlers.',
                'Clarify admin help text for WhatsApp share templates and OG preview requirements (EN/MS).',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki pratonton kongsi WhatsApp produk tanpa imej OG apabila mesej custom tiada URL produk.',
            'changes' => [
                'Sentiasa tambah URL produk pada teks kongsi WhatsApp jika templat custom tiada {product_url}.',
                'Baiki Open Graph produk: imej SEO og pilihan, URL imej HTTPS, dan og:image:type untuk crawler.',
                'Jelaskan bantuan admin untuk templat kongsi WhatsApp dan keperluan pratonton OG (EN/MS).',
            ],
        ],
    ],
    '4.7.69' => [
        'en' => [
            'summary' => 'Production cache resilience when Redis is down and cleaner admin alert dismiss accessibility.',
            'changes' => [
                'Fallback to file sessions and disable app cache when Redis is unreachable, without Predis alert spam in laravel.log.',
                'Fix admin alert close buttons: shared partial with aria-label and decorative icons hidden from assistive tech only.',
                'Add admin Close/Tutup translation for dismiss buttons across settings, products, blog, import, and OneSender pages.',
            ],
        ],
        'ms' => [
            'summary' => 'Ketahanan cache production apabila Redis down dan aksesibiliti butang tutup alert admin lebih bersih.',
            'changes' => [
                'Fallback ke session fail dan nyahaktif cache app apabila Redis tidak boleh dicapai, tanpa spam alert Predis dalam laravel.log.',
                'Baiki butang tutup alert admin: partial kongsi dengan aria-label dan ikon hiasan disembunyikan dari assistive tech sahaja.',
                'Tambah terjemahan admin Tutup untuk butang dismiss merentasi settings, produk, blog, import, dan halaman OneSender.',
            ],
        ],
    ],
    '4.7.68' => [
        'en' => [
            'summary' => 'Fix 500 Server Error after deploy caused by stale route cache missing the home route.',
            'changes' => [
                'Purge all routes-v7*.php cache files on every boot since route caching is disabled for module routes.',
                'Block route:trans:cache and clear both route:clear and route:trans:clear during version updates.',
                'Harden 404 page and storefront_home_url() when the home route is temporarily unavailable.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki ralat 500 selepas deploy disebabkan route cache lama yang tiada route home.',
            'changes' => [
                'Padam semua fail cache routes-v7*.php setiap boot kerana route caching dinyahaktifkan untuk route modul.',
                'Sekat route:trans:cache dan kosongkan route:clear serta route:trans:clear semasa kemas kini versi.',
                'Kukuhkan halaman 404 dan storefront_home_url() apabila route home tidak tersedia sementara.',
            ],
        ],
    ],
    '4.7.67' => [
        'en' => [
            'summary' => 'Mobile product page app UX, WhatsApp share with Open Graph, navigation polish, and route cache guard.',
            'changes' => [
                'Redesign mobile product page with toolbar breadcrumb, bottom-sheet variant picker, sticky cart dock, and compact meta tabs.',
                'Fix variant gallery refresh on treatment selection and add 3D active tab styling on product details.',
                'Add admin toggle and message template for WhatsApp product share; improve OG image/title/description for link previews.',
                'Refresh mobile navigation, footer, category menu, and sidebar menu layout.',
                'Add route cache guard and optimize artisan commands for safer production caching.',
            ],
        ],
        'ms' => [
            'summary' => 'UX halaman produk mobile ala app, kongsi WhatsApp dengan Open Graph, kemas navigation, dan pengawal route cache.',
            'changes' => [
                'Reka semula halaman produk mobile dengan breadcrumb toolbar, picker variant bottom sheet, dock cart, dan tab meta padat.',
                'Baiki refresh gallery variant apabila pilih rawatan dan tambah gaya tab aktif 3D pada butiran produk.',
                'Tambah toggle admin dan templat mesej kongsi WhatsApp produk; baiki OG imej/tajuk/penerangan untuk pratonton pautan.',
                'Kemas kini susun atur navigation mobile, footer, menu kategori, dan sidebar.',
                'Tambah pengawal route cache dan arahan optimize artisan untuk caching production lebih selamat.',
            ],
        ],
    ],
    '4.7.66' => [
        'en' => [
            'summary' => 'Speed up registration and storefront pages with query cache, deferred post-register jobs, and production .env template.',
            'changes' => [
                'Defer welcome WhatsApp, wallet, and referral work until after the HTTP response so register saves to DB in ~200ms.',
                'Fix sync queue blocking registration for ~30s on OneSender welcome WhatsApp API calls.',
                'Cache homepage featured categories, blog posts, and product listing price range; memoize mega menu per request.',
                'Add guest homepage full-page HTML cache (RESPONSE_CACHE_HOME_ENABLED) and production .env.production.example.',
                'Block real OneSender sends on APP_ENV=local unless ONESENDER_ALLOW_IN_LOCAL=true; fix XAMPP tagged cache writes.',
            ],
        ],
        'ms' => [
            'summary' => 'Percepatkan pendaftaran dan halaman storefront dengan cache query, job selepas daftar, dan template .env production.',
            'changes' => [
                'Tangguh WhatsApp alu-aluan, wallet, dan rujukan sehingga selepas response HTTP supaya daftar simpan DB ~200ms.',
                'Baiki sync queue yang block pendaftaran ~30s semasa panggilan API WhatsApp alu-aluan OneSender.',
                'Cache kategori pilihan, blog homepage, dan julat harga senarai produk; memo menu mega setiap request.',
                'Tambah cache HTML homepage tetamu (RESPONSE_CACHE_HOME_ENABLED) dan .env.production.example.',
                'Sekat hantar OneSender sebenar pada APP_ENV=local melainkan ONESENDER_ALLOW_IN_LOCAL=true; baiki cache bertag XAMPP.',
            ],
        ],
    ],
    '4.7.65' => [
        'en' => [
            'summary' => 'Fix homepage product tab sliders, mobile controls, register phone validation, and Google Reviews mobile layout.',
            'changes' => [
                'Fix Alpine errors and broken PREV/NEXT controls on homepage product tab sliders after tab switch.',
                'Revamp mobile slider navigation with circular buttons, slide counter, and persistent Swiper instances.',
                'Fix register form rejecting valid Malaysian phone numbers and add referral code field icon.',
                'Redesign Google Reviews section for mobile with short tab labels and updated subheading layout.',
                'Stop CacheHealth tagged-cache probe and local Vite manifest errors from spamming laravel.log.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki slider tab produk homepage, kawalan mobile, pengesahan telefon daftar, dan susun atur Google Reviews mobile.',
            'changes' => [
                'Baiki ralat Alpine dan kawalan PREV/NEXT slider tab produk homepage selepas tukar tab.',
                'Kemas kini navigasi slider mobile dengan butang bulat, penunjuk slaid, dan instance Swiper kekal.',
                'Baiki borang daftar menolak nombor telefon Malaysia sah dan tambah ikon medan kod rujukan.',
                'Reka semula bahagian Google Reviews untuk mobile dengan label tab pendek dan susun atur subheading baharu.',
                'Hentikan ujian cache bertag CacheHealth dan ralat manifest Vite tempatan daripada membanjiri laravel.log.',
            ],
        ],
    ],
    '4.7.64' => [
        'en' => [
            'summary' => 'Fix homepage product tab badges clipped on mobile.',
            'changes' => [
                'Fix Latest Treatments and other product tab pills being cut off on the left edge of mobile homepage cards.',
                'Remove negative-margin tab header overflow and disable legacy underline pseudo-elements on mobile pill tabs.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki badge tab produk homepage terpotong pada mobile.',
            'changes' => [
                'Baiki pill tab seperti Latest Treatments terpotong di tepi kiri kad homepage mobile.',
                'Buang margin negatif header tab dan matikan pseudo underline legacy pada tab pill mobile.',
            ],
        ],
    ],
    '4.7.63' => [
        'en' => [
            'summary' => 'Homepage load performance and mobile search bar visibility fix.',
            'changes' => [
                'Defer cart, compare, and wishlist API calls until the browser is idle to reduce main-thread load on page open.',
                'Lazy-init below-fold Swiper carousels and paint search suggestion updates after the first frame.',
                'Fix mobile search bar clipped off-screen by overriding desktop translateY(-50%) on the active search form.',
            ],
        ],
        'ms' => [
            'summary' => 'Prestasi muat laman utama dan baiki bar carian mobile yang tidak kelihatan.',
            'changes' => [
                'Tunda panggilan API cart, compare, dan wishlist sehingga browser idle untuk kurangkan beban thread utama semasa buka halaman.',
                'Init carousel Swiper bawah fold secara lazy dan kemas kini cadangan carian selepas frame pertama.',
                'Baiki bar carian mobile terpotong di luar skrin dengan override translateY(-50%) desktop pada borang carian aktif.',
            ],
        ],
    ],
    '4.7.62' => [
        'en' => [
            'summary' => 'Mobile cookie bar, Google Reviews app layout, and full-screen search overlay.',
            'changes' => [
                'Fix cookie consent bar on mobile so it sits above the bottom navigation and stays fully readable.',
                'Restyle Google Reviews for mobile with peek carousel, compact rating summary, and PREV/NEXT controls.',
                'Replace mobile header search with a full-screen overlay that hides duplicate header chrome and locks scroll.',
            ],
        ],
        'ms' => [
            'summary' => 'Bar kuki mobile, susun atur Google Reviews gaya app, dan overlay carian skrin penuh.',
            'changes' => [
                'Baiki bar persetujuan kuki pada mobile supaya berada di atas navigasi bawah dan boleh dibaca sepenuhnya.',
                'Gaya semula Google Reviews untuk mobile dengan carousel peek, ringkasan rating padat, dan kawalan PREV/NEXT.',
                'Ganti carian header mobile dengan overlay skrin penuh yang menyembunyikan header berganda dan mengunci skrol.',
            ],
        ],
    ],
    '4.7.61' => [
        'en' => [
            'summary' => 'Homepage mobile app layout with card sections, carousels, and aligned footer.',
            'changes' => [
                'Add home-page mobile shell with iOS-style gray canvas and white section cards.',
                'Restyle hero slider with side padding, rounded corners, gradient overlay, and centered dots.',
                'Convert blog and featured category product rows into swipe carousels with PREV/NEXT controls.',
                'Center featured category tabs and improve mobile product slider navigation buttons.',
                'Align footer horizontal padding with homepage section cards on mobile.',
            ],
        ],
        'ms' => [
            'summary' => 'Susun atur app mobile laman utama dengan kad seksyen, carousel, dan footer sejajar.',
            'changes' => [
                'Tambah shell mobile home-page dengan kanvas kelabu gaya iOS dan kad seksyen putih.',
                'Gaya semula slider hero dengan padding sisi, sudut bulat, overlay gradien, dan dot berpusat.',
                'Tukar baris blog dan produk kategori pilihan kepada carousel swipe dengan kawalan PREV/NEXT.',
                'Pusatkan tab kategori pilihan dan baiki butang navigasi slider produk mobile.',
                'Selaraskan padding mendatar footer dengan kad seksyen laman utama pada mobile.',
            ],
        ],
    ],
    '4.7.60' => [
        'en' => [
            'summary' => 'Customer account mobile app UX, loyalty stamps, order reviews, and admin stamp program tools.',
            'changes' => [
                'Standardize mobile back header across all account subpages (orders, profile, rewards, appointments).',
                'Redesign account dashboard menu as iOS-style grouped cards with colored icons and badges.',
                'Revamp profile page for mobile: hero, stamp cards, sticky save bar, and compact form rows.',
                'Restyle My Appointments and My Rewards pages with matching mobile cards and overflow fixes.',
                'Add order detail rewards, product reviews, and mobile card layouts on account order pages.',
                'Improve admin stamp program editor with product picker, preview card, and eligible product rules.',
                'Preload Line Awesome fonts to reduce mobile console warnings; tighten footer mobile spacing.',
            ],
        ],
        'ms' => [
            'summary' => 'UX app mobile akaun pelanggan, kad setem, ulasan pesanan, dan alat program setem admin.',
            'changes' => [
                'Seragamkan header back mobile merentas semua subhalaman akaun (pesanan, profil, ganjaran, temujanji).',
                'Reka semula menu dashboard akaun sebagai kad berkumpulan gaya iOS dengan ikon berwarna dan lencana.',
                'Revamp halaman profil untuk mobile: hero, kad setem, bar simpan melekit, dan baris borang padat.',
                'Gaya semula Temujanji Saya dan Ganjaran Saya dengan kad mobile sepadan dan baiki limpahan skrin.',
                'Tambah ganjaran pesanan, ulasan produk, dan susun atur kad mobile pada halaman butiran pesanan.',
                'Baiki editor program setem admin dengan pemilih produk, pratonton kad, dan peraturan produk layak.',
                'Pramuat fon Line Awesome untuk kurangkan amaran konsol mobile; ketatkan jarak footer mobile.',
            ],
        ],
    ],
    '4.7.59' => [
        'en' => [
            'summary' => 'Storefront mobile UX, customer WhatsApp OTP login, and product slider improvements.',
            'changes' => [
                'Add WhatsApp OTP login tabs on customer login with OneSender API integration.',
                'Fix phone input country selector init and clarify Login with OTP labels (EN/MS).',
                'Remove confusing WhatsApp OTP button from register; keep OTP on login only.',
                'Redesign mobile footer as app-style accordion cards and tighten copyright spacing.',
                'Modernize bottom navigation as iOS-style tab bar with safe-area support.',
                'Improve product sliders with mobile swipe bullets, centered controls, and dynamic pagination.',
                'Make hero slider and home sections responsive on small screens.',
            ],
        ],
        'ms' => [
            'summary' => 'UX mobile storefront, log masuk OTP WhatsApp pelanggan, dan penambahbaikan slider produk.',
            'changes' => [
                'Tambah tab log masuk OTP WhatsApp pelanggan dengan integrasi API OneSender.',
                'Baiki init pemilih kod negara telefon dan jelaskan label Log masuk dengan OTP (EN/MS).',
                'Buang butang OTP WhatsApp yang mengelirukan dari register; kekalkan OTP di login sahaja.',
                'Reka semula footer mobile sebagai kad accordion gaya app dan ketatkan jarak copyright.',
                'Modenkan navigasi bawah sebagai tab bar gaya iOS dengan sokongan safe-area.',
                'Baiki slider produk dengan bullet swipe mobile, kawalan berpusat, dan pagination dinamik.',
                'Jadikan slider hero dan seksyen laman utama responsif pada skrin kecil.',
            ],
        ],
    ],
    '4.7.58' => [
        'en' => [
            'summary' => 'Storefront admin multi-column layouts for homepage sections, banners, and product tabs.',
            'changes' => [
                'Reorganize Featured Categories into a compact two-column grid with stacked product fields.',
                'Apply side-by-side layouts to Product Tabs One/Two, Product Grid, and Flash Sale sections.',
                'Modernize all banner tabs (slider, two/three column, full-width) with section cards and grids.',
                'Update Top Brands, Blogs, Newsletter, Google Reviews, and Product Page tabs to match settings UI.',
                'Add reusable product_tab_block and banner_block partials for consistent storefront admin forms.',
            ],
        ],
        'ms' => [
            'summary' => 'Susun atur berbilang lajur admin Storefront untuk seksyen laman utama, banner, dan tab produk.',
            'changes' => [
                'Susun semula Featured Categories dalam grid dua lajur padat dengan medan produk bertindan.',
                'Gunakan susun atur sebelah-menyebelah untuk Product Tabs One/Two, Product Grid, dan Flash Sale.',
                'Modenkan semua tab banner (slider, dua/tiga lajur, lebar penuh) dengan kad seksyen dan grid.',
                'Kemas kini tab Top Brands, Blogs, Newsletter, Google Reviews, dan Product Page mengikut UI tetapan.',
                'Tambah partial product_tab_block dan banner_block untuk borang admin storefront yang konsisten.',
            ],
        ],
    ],
    '4.7.57' => [
        'en' => [
            'summary' => 'Modern SaaS admin UI for storefront settings and project-wide drag-and-drop image uploads.',
            'changes' => [
                'Revamp Storefront admin settings with sidebar layout, section cards, and improved tab navigation.',
                'Replace legacy browse image pickers with drag-and-drop upload zones across admin settings.',
                'Add compact logo, banner, and square image preview modes with proper scaling.',
                'Reorganize Logo, Footer, and Features tabs with efficient multi-column field layouts.',
                'Hide native file inputs and fix CSS conflicts with legacy image-holder styles.',
                'Add EN/MS translations for new media picker and storefront section labels.',
            ],
        ],
        'ms' => [
            'summary' => 'UI admin SaaS moden untuk tetapan storefront dan muat naik imej drag-and-drop di seluruh projek.',
            'changes' => [
                'Revamp tetapan admin Storefront dengan susun atur sidebar, kad seksyen, dan navigasi tab yang lebih baik.',
                'Ganti pemilih imej browse lama dengan zon muat naik drag-and-drop merentas tetapan admin.',
                'Tambah mod pratonton imej logo, banner, dan segi empat yang padat dengan penskalaan betul.',
                'Susun semula tab Logo, Footer, dan Features dengan susun atur medan berbilang lajur.',
                'Sembunyikan input fail asli dan baiki konflik CSS dengan gaya image-holder lama.',
                'Tambah terjemahan EN/MS untuk pemilih media baharu dan label seksyen storefront.',
            ],
        ],
    ],
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
