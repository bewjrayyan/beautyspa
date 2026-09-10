<?php

/**
 * Release changelog shown in Admin → Settings → System after each version update.
 * Add a new entry whenever app/AestheticCart.php VERSION is bumped.
 */
return [
    '4.11.80' => [
        'date' => '2026-09-10',
        'en' => [
            'summary' => 'Add the Lead Central Management module with a fully live operations overview.',
            'changes' => [
                'New Lead workspace: leads management, import, wallet, customer view and reporting.',
                'Central dashboard now renders real-time data for all KPIs, targets, leaderboards and operations (check-in, clearance, payments) — no hardcoded numbers.',
                'Payment verification checklist and evidence review flows with expiring signed receipt previews.',
                'Order admin header adds customer segment, purchase count/ordinal, birthday meta and status pipeline; returning-customer detection now includes phone.',
                'Treatment reservation CRM shows customer visit labels and purchase history on orders.',
                'New admin.leads.* permissions granted to admin roles; rebuilt admin front-end assets.',
            ],
        ],
        'ms' => [
            'summary' => 'Tambah modul Lead Central Management dengan paparan operasi yang sepenuhnya live.',
            'changes' => [
                'Ruang kerja Lead baharu: pengurusan lead, import, wallet, paparan pelanggan dan pelaporan.',
                'Dashboard pusat kini paparkan data masa nyata untuk semua KPI, sasaran, senarai pendahulu dan operasi (check-in, clearance, bayaran) — tiada lagi nombor statik.',
                'Senarai semak pengesahan bayaran dan aliran semakan bukti dengan pratonton resit bertandatangan yang tamat tempoh.',
                'Header pesanan admin menambah segmen pelanggan, kiraan/kekerapan pembelian, meta hari jadi dan saluran status; pengesanan pelanggan berulang kini termasuk telefon.',
                'CRM tempahan rawatan menunjukkan label lawatan pelanggan dan sejarah pembelian pada pesanan.',
                'Kebenaran admin.leads.* baharu diberikan kepada peranan admin; aset front-end admin dibina semula.',
            ],
        ],
    ],

    '4.11.79' => [
        'date' => '2026-09-09',
        'en' => [
            'summary' => 'Fix CHIP card checkout rejecting invalid whitelist code “card”.',
            'changes' => [
                'Default card whitelist now uses visa/mastercard network codes CHIP accepts.',
                'Legacy admin value “card” is expanded to real card methods from the CHIP API.',
                'Clarify Settings help and docs so “card” is not used as a CHIP method code.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki checkout kad CHIP yang menolak kod whitelist “card” yang tidak sah.',
            'changes' => [
                'Whitelist kad lalai kini guna kod rangkaian visa/mastercard yang CHIP terima.',
                'Nilai admin legasi “card” dikembangkan kepada kaedah kad sebenar daripada API CHIP.',
                'Perjelas bantuan Settings dan docs supaya “card” tidak digunakan sebagai kod kaedah CHIP.',
            ],
        ],
    ],

    '4.11.78' => [
        'date' => '2026-09-07',
        'en' => [
            'summary' => 'Harden checkout against Redis/availability outages during booking.',
            'changes' => [
                'Skip availability/dates requests for TBA or unset schedule mode to avoid unnecessary 503 noise.',
                'Degrade availability/dates to an empty calendar instead of failing hard on Redis/server errors.',
                'Silence generic Something went wrong toasts for transient cart 5xx responses and clarify place-order busy errors.',
            ],
        ],
        'ms' => [
            'summary' => 'Kuatkan checkout terhadap gangguan Redis/ketersediaan semasa tempahan.',
            'changes' => [
                'Langkau permintaan availability/dates untuk mod TBA atau belum dipilih supaya elak hingar 503 yang tak perlu.',
                'Degrade availability/dates kepada kalendar kosong berbanding gagal keras bila Redis/server ralat.',
                'Senyapkan toast Something went wrong generik untuk respons cart 5xx sementara dan perjelas ralat place-order sibuk.',
            ],
        ],
    ],

    '4.11.77' => [
        'date' => '2026-09-07',
        'en' => [
            'summary' => 'Clarify checkout shipment items and treatment variation details.',
            'changes' => [
                'Show which physical products are included under Shipping Method.',
                'Display selected treatment variations in booking cards and Appointment Details.',
                'Fix broken shipping markup that pushed Order Summary below the main column.',
            ],
        ],
        'ms' => [
            'summary' => 'Perjelas item penghantaran checkout dan butiran variasi rawatan.',
            'changes' => [
                'Tunjukkan produk fizikal yang disertakan di bawah Kaedah penghantaran.',
                'Paparkan variasi rawatan dipilih pada kad tempahan dan Butiran temujanji.',
                'Baiki markup penghantaran rosak yang menolak Ringkasan pesanan ke bawah kolum utama.',
            ],
        ],
    ],

    '4.11.76' => [
        'date' => '2026-09-07',
        'en' => [
            'summary' => 'Place checkout sign-in password and button side by side.',
            'changes' => [
                'Lay out the returning-customer password field and Sign in & continue button in one row on wider screens.',
                'Keep the sign-in controls stacked on small screens for easier tapping.',
            ],
        ],
        'ms' => [
            'summary' => 'Letak medan kata laluan dan butang log masuk checkout sebelah menyebelah.',
            'changes' => [
                'Susun medan kata laluan pelanggan sedia ada dan butang Sign in & continue dalam satu baris pada skrin lebih lebar.',
                'Kekalkan kawalan log masuk bertindan pada skrin kecil untuk ketukan lebih mudah.',
            ],
        ],
    ],

    '4.11.75' => [
        'date' => '2026-09-07',
        'en' => [
            'summary' => 'Fix checkout account gate Alpine crash for guest/create paths.',
            'changes' => [
                'Initialize create_an_account and password fields on the checkout form so guest vs create-account toggles no longer throw.',
                'Harden account-mode aria-selected bindings against undefined values.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki ranap Alpine pintu akaun checkout untuk laluan tetamu/cipta akaun.',
            'changes' => [
                'Mulakan medan create_an_account dan kata laluan pada borang checkout supaya togol tetamu vs cipta akaun tidak lagi ralat.',
                'Kuatkan pengikatan aria-selected mod akaun terhadap nilai undefined.',
            ],
        ],
    ],

    '4.11.74' => [
        'date' => '2026-09-07',
        'en' => [
            'summary' => 'Modernize checkout account gate for easier sign-in or guest checkout.',
            'changes' => [
                'Redesign Account Details into an email-first gate with clear Sign in, Create account, and Continue as guest paths.',
                'Add password visibility toggles on checkout login and registration fields.',
                'Polish signed-in account state with a clearer status badge and supporting copy.',
            ],
        ],
        'ms' => [
            'summary' => 'Modenkan pintu akaun checkout untuk log masuk atau checkout tetamu lebih mudah.',
            'changes' => [
                'Reka semula Butiran akaun menjadi pintu berasaskan e-mel dengan laluan Log masuk, Cipta akaun, dan Teruskan sebagai tetamu yang jelas.',
                'Tambah togol nampak kata laluan pada medan log masuk dan pendaftaran checkout.',
                'Perhalusi keadaan sudah log masuk dengan lencana status dan teks sokongan yang lebih jelas.',
            ],
        ],
    ],

    '4.11.73' => [
        'date' => '2026-09-07',
        'en' => [
            'summary' => 'Fix checkout email check hanging on server/Redis errors.',
            'changes' => [
                'Harden checkout/check-email so cache or Redis failures degrade safely instead of returning 500.',
                'Clear the Checking email spinner with request timeouts and ignore stale responses.',
                'Broaden Redis exception detection for JSON checkout requests and keep guest JSON probes from hard-redirecting.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki semakan e-mel checkout yang tergantung akibat ralat server/Redis.',
            'changes' => [
                'Kukuhkan checkout/check-email supaya kegagalan cache atau Redis merosot dengan selamat dan tidak pulangkan 500.',
                'Kosongkan spinner Menyemak e-mel dengan had masa permintaan dan abaikan respons lama.',
                'Perluaskan pengesanan ralat Redis untuk permintaan JSON checkout dan elakkan redirect keras pada probe tetamu JSON.',
            ],
        ],
    ],

    '4.11.72' => [
        'date' => '2026-09-07',
        'en' => [
            'summary' => 'Fix SweetAlert2 success icons and notification message rendering.',
            'changes' => [
                'Restore native SweetAlert2 icon geometry so success/error marks no longer break or spill outside the modal.',
                'Scope modal CSS variables to the popup, align admin/storefront alerts to a consistent 480px layout, and clip timer/overflow artifacts.',
                'Coerce API/object flash payloads into readable messages and show session flashes one after another.',
                'Load SweetAlert styles on auth pages so login notifications match the rest of the app.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki ikon kejayaan SweetAlert2 dan paparan mesej notifikasi.',
            'changes' => [
                'Pulihkan geometri ikon SweetAlert2 supaya tanda kejayaan/ralat tidak rosak atau keluar dari modal.',
                'Scope pembolehubah CSS modal ke popup, seragamkan alert admin/storefront kepada layout 480px, dan potong artifak timer/overflow.',
                'Tukar payload flash API/objek kepada mesej yang boleh dibaca dan paparkan flash sesi secara berurutan.',
                'Muatkan gaya SweetAlert pada halaman auth supaya notifikasi log masuk sama seperti seluruh aplikasi.',
            ],
        ],
    ],

    '4.11.71' => [
        'date' => '2026-09-07',
        'en' => [
            'summary' => 'Add product shipping classes with accurate Flat Rate costing and a clearer physical-product checkout.',
            'changes' => [
                'Add admin Shipping Classes CRUD, permissions, and a required shipping class field on physical products.',
                'Calculate Flat Rate from class cost × quantity (with safe fallbacks) so physical orders are not undercharged.',
                'Hide treatment/spa booking UI for physical-only carts and align Shipping Method cards with Payment Method styling.',
                'Add a Proceed button to jump to Order Summary, plus backfill/grant commands, destroy guards, and resolver unit tests.',
            ],
        ],
        'ms' => [
            'summary' => 'Tambah kelas penghantaran produk dengan kos Flat Rate tepat dan checkout produk fizikal yang lebih jelas.',
            'changes' => [
                'Tambah CRUD Kelas Penghantaran admin, kebenaran, dan medan kelas wajib pada produk fizikal.',
                'Kira Flat Rate dari kos kelas × kuantiti (dengan fallback selamat) supaya pesanan fizikal tidak kurang caj.',
                'Sembunyikan UI tempahan rawatan/spa untuk cart fizikal sahaja dan seragamkan kad Shipping Method dengan Payment Method.',
                'Tambah butang Proceed ke Order Summary, plus arahan backfill/grant, kawalan padam, dan ujian unit resolver.',
            ],
        ],
    ],

    '4.11.70' => [
        'date' => '2026-09-07',
        'en' => [
            'summary' => 'Polish Treatment POS booking UX and harden production Redis failures on checkout.',
            'changes' => [
                'Improve POS customer desk, receipt upload, catalog pagination, empty-cart messaging, and real product thumbnails in the cart.',
                'Speed up branch availability lookups and add checkout-style loyalty points redeem in the POS booking summary.',
                'Add a top-nav POS Booking shortcut and refine POS side-rail layout for faster walk-in booking.',
                'Handle Redis NOAUTH/connectivity failures with safer cache/session fallback so checkout no longer shows raw Predis errors.',
            ],
        ],
        'ms' => [
            'summary' => 'Perhalusi UX tempahan Treatment POS dan kukuhkan kegagalan Redis production pada checkout.',
            'changes' => [
                'Tingkatkan desk pelanggan POS, muat naik resit, penomboran katalog, mesej cart kosong, dan thumbnail produk sebenar dalam cart.',
                'Percepat carian availability cawangan dan tambah tebus mata loyalty bergaya checkout dalam ringkasan tempahan POS.',
                'Tambah pintasan POS Booking pada top-nav dan perhalusi layout side-rail POS untuk tempahan walk-in lebih pantas.',
                'Tangani kegagalan Redis NOAUTH/sambungan dengan fallback cache/session yang lebih selamat supaya checkout tidak memaparkan ralat Predis mentah.',
            ],
        ],
    ],

    '4.11.69' => [
        'date' => '2026-09-07',
        'en' => [
            'summary' => 'Launch a production-hardened Treatment POS booking workspace.',
            'changes' => [
                'Add guided per-treatment booking for customer, variant, options, spa branch, beautician, date, and live availability slot selection.',
                'Prevent overlapping customer and beautician appointments across POS and storefront bookings, including multi-treatment carts.',
                'Require offline payment receipts, store them privately, and protect authorized receipt access from public URL and srcset exposure.',
                'Add membership lookup, customer search, idempotent booking submission, status workflow, audit activity, and role-scoped POS APIs.',
                'Add database hardening and automated regression coverage for availability, security, private media, and POS request integrity.',
            ],
        ],
        'ms' => [
            'summary' => 'Lancarkan ruang kerja tempahan Treatment POS yang diperkukuh untuk production.',
            'changes' => [
                'Tambah aliran tempahan berpandu bagi setiap rawatan untuk pelanggan, varian, pilihan, cawangan spa, beautician, tarikh, dan slot availability langsung.',
                'Cegah pertindihan masa pelanggan dan beautician antara tempahan POS dan storefront, termasuk cart berbilang rawatan.',
                'Wajibkan resit bayaran offline, simpan secara private, dan lindungi akses resit daripada URL awam serta pendedahan srcset.',
                'Tambah carian membership dan pelanggan, penghantaran tempahan idempotent, aliran status, audit aktiviti, dan API POS mengikut skop peranan.',
                'Tambah pengukuhan database dan regression test automatik untuk availability, security, private media, dan integriti request POS.',
            ],
        ],
    ],

    '4.11.68' => [
        'date' => '2026-09-04',
        'en' => [
            'summary' => 'Deliver a responsive mobile workspace for the beautician portal.',
            'changes' => [
                'Add consistent mobile navigation across the dashboard, job sheet, calendar, availability, and account pages.',
                'Refine mobile dashboard metrics, needs-attention cards, and monthly calendar into compact two-column and touch-friendly layouts.',
                'Replace the cramped weekly calendar grid on mobile with a readable seven-day appointment agenda.',
                'Modernize payment receipt uploads with a responsive drag-and-drop zone, file preview, keyboard access, and remove action.',
            ],
        ],
        'ms' => [
            'summary' => 'Sediakan ruang kerja mobile responsif untuk portal beautician.',
            'changes' => [
                'Tambah navigasi mobile yang konsisten pada dashboard, job sheet, kalendar, availability, dan halaman akaun.',
                'Perhalusi metrik dashboard, kad needs-attention, dan kalendar bulanan mobile menjadi susun atur dua kolum yang kompak serta mesra sentuhan.',
                'Ganti grid kalendar mingguan mobile yang sempit dengan agenda temujanji tujuh hari yang mudah dibaca.',
                'Modenkan muat naik resit bayaran dengan dropzone responsif, preview fail, akses papan kekunci, dan tindakan buang.',
            ],
        ],
    ],

    '4.11.67' => [
        'date' => '2026-09-04',
        'en' => [
            'summary' => 'Refine admin order filtering and treatment activity history.',
            'changes' => [
                'Reorganize order filters into a responsive command panel with clearer channel, payment status, period, and search controls.',
                'Add an active-filter summary while preserving existing filter URLs and order table behavior.',
                'Present treatment activity as a chronological timeline with action icons, timestamps, actor details, and readable note callouts.',
            ],
        ],
        'ms' => [
            'summary' => 'Perhalusi penapisan pesanan admin dan sejarah aktiviti rawatan.',
            'changes' => [
                'Susun semula penapis pesanan sebagai panel responsif dengan kawalan saluran, status bayaran, tempoh, dan carian yang lebih jelas.',
                'Tambah ringkasan penapis aktif sambil mengekalkan URL penapis dan tingkah laku jadual pesanan sedia ada.',
                'Papar aktiviti rawatan sebagai garis masa kronologi dengan ikon tindakan, masa, pelaksana, dan callout nota yang mudah dibaca.',
            ],
        ],
    ],

    '4.11.66' => [
        'date' => '2026-09-04',
        'en' => [
            'summary' => 'Modernize report dashboards and harden report filtering and exports.',
            'changes' => [
                'Unify sales, coupon, customer, and beautician reporting with clearer SaaS dashboards and readable activity tables.',
                'Improve report filters, date controls, KPI cards, branch summaries, sales trends, and beautician contribution insights.',
                'Validate report inputs, remove invalid grouping paths, and keep dashboard metrics aligned with active report filters.',
                'Protect spreadsheet exports from formula injection, enforce explicit row limits, and fix XLSX response handling.',
            ],
        ],
        'ms' => [
            'summary' => 'Modenkan dashboard laporan serta kukuhkan penapisan dan eksport laporan.',
            'changes' => [
                'Seragamkan laporan jualan, kupon, pelanggan, dan beautician dengan dashboard SaaS yang lebih jelas serta jadual aktiviti mudah dibaca.',
                'Perhalusi penapis laporan, kawalan tarikh, kad KPI, ringkasan cawangan, trend jualan, dan insight sumbangan beautician.',
                'Sahkan input laporan, buang aliran grouping yang tidak sah, dan selaraskan metrik dashboard dengan penapis laporan aktif.',
                'Lindungi eksport spreadsheet daripada formula injection, kuatkuasakan had baris yang jelas, dan baiki pengendalian respons XLSX.',
            ],
        ],
    ],

    '4.11.65' => [
        'date' => '2026-09-02',
        'en' => [
            'summary' => 'Refine admin products index stats and filter layout.',
            'changes' => [
                'Split catalog stats and product filters into separate overview and filters sections.',
                'Use flat theme-accent filter chips without shadows for active and hover states.',
            ],
        ],
        'ms' => [
            'summary' => 'Perhalusi susun atur statistik dan penapis senarai produk admin.',
            'changes' => [
                'Pisahkan statistik katalog dan penapis produk ke section ringkasan dan penapis.',
                'Guna chip penapis flat dengan warna tema tanpa shadow untuk keadaan aktif dan hover.',
            ],
        ],
    ],

    '4.11.64' => [
        'date' => '2026-09-02',
        'en' => [
            'summary' => 'Advanced admin product filters with premium catalog toolbar.',
            'changes' => [
                'Add quick filters for status, type, and stock on a single wrapping row.',
                'Add advanced filters for category, brand, tag, price, sale, SKU, and date range.',
                'Sync filter state with URL and DataTable server-side query via ProductIndexQueryFilter.',
            ],
        ],
        'ms' => [
            'summary' => 'Penapis produk admin lanjutan dengan toolbar katalog premium.',
            'changes' => [
                'Tambah penapis pantas status, jenis, dan stok dalam satu baris yang boleh wrap.',
                'Tambah penapis lanjutan kategori, jenama, tag, harga, jualan, SKU, dan julat tarikh.',
                'Selaraskan keadaan penapis dengan URL dan pertanyaan server DataTable melalui ProductIndexQueryFilter.',
            ],
        ],
    ],

    '4.11.63' => [
        'date' => '2026-09-02',
        'en' => [
            'summary' => 'Bulk disable products and restore appointment preview drawer title alignment.',
            'changes' => [
                'Add bulk Disable action on admin products index for selected rows.',
                'Restore preview drawer title font size and align CRM chips beside the heading.',
            ],
        ],
        'ms' => [
            'summary' => 'Nyahaktif produk secara pukal dan baiki alignment tajuk preview temujanji.',
            'changes' => [
                'Tambah tindakan Nyahaktif pukal pada senarai produk admin untuk baris terpilih.',
                'Pulihkan saiz fon tajuk preview drawer dan align chip CRM di sebelah tajuk.',
            ],
        ],
    ],

    '4.11.62' => [
        'date' => '2026-09-01',
        'en' => [
            'summary' => 'Work-log notes stamp completion time per checklist item, not appointment slot.',
            'changes' => [
                'Generate customer note uses today\'s completion time instead of the appointment schedule date.',
                'Each completed checklist task keeps its own date and time as the work log is updated through the day.',
                'Save and reload preserve per-item completed_at stamps in the treatment work log.',
            ],
        ],
        'ms' => [
            'summary' => 'Nota work log stamp masa siap setiap item senarai semak, bukan slot temujanji.',
            'changes' => [
                'Jana nota pelanggan guna masa siap hari ini, bukan tarikh jadual temujanji.',
                'Setiap tugasan senarai semak yang selesai menyimpan tarikh dan masa sendiri sepanjang hari.',
                'Simpan dan muat semula mengekalkan stamp completed_at setiap item dalam log kerja rawatan.',
            ],
        ],
    ],

    '4.11.61' => [
        'date' => '2026-09-01',
        'en' => [
            'summary' => 'Admin order show workflow UX, payment-proof signed URLs, and job-sheet deep link.',
            'changes' => [
                'Admin order show: consolidate Actions dropdown, Manage order statuses, inline stamp cards beside Total, and premium glass appointment cards.',
                'Fix subdirectory signed URLs for private payment-proof links (legacy + corrected signatures).',
                'Manage in job sheet opens the beautician calendar focused on that appointment details drawer.',
            ],
        ],
        'ms' => [
            'summary' => 'UX workflow paparan pesanan admin, URL ditandatangani bukti bayaran, dan pautan terus job sheet.',
            'changes' => [
                'Paparan pesanan admin: dropdown Actions, status Manage order, stamp cards sebaris dengan Total, dan kad temujanji glass premium.',
                'Baiki URL ditandatangani subdirectory untuk pautan bukti bayaran peribadi (tandatangan legacy + baharu).',
                'Manage in job sheet membuka kalendar beautician terus ke butiran appointment tersebut.',
            ],
        ],
    ],

    '4.11.60' => [
        'date' => '2026-08-28',
        'en' => [
            'summary' => 'Unified 80mm thermal receipt with WhatsApp and download across checkout, admin, and account.',
            'changes' => [
                'Checkout complete, admin order receipt, beautician portal, and customer account share the same thermal receipt layout.',
                'Replace print dialog with WhatsApp send and PDF download buttons below the receipt card.',
                'Receipt PDF uses the same HTML template as the web view, sized for 80mm thermal paper.',
                'Fix missing checkout summary translation keys for beautician and schedule pending states.',
            ],
        ],
        'ms' => [
            'summary' => 'Resit thermal 80mm diseragamkan dengan WhatsApp dan muat turun di checkout, admin, dan akaun.',
            'changes' => [
                'Checkout complete, resit pesanan admin, portal beautician, dan akaun pelanggan guna layout resit thermal yang sama.',
                'Ganti dialog cetak dengan butang hantar WhatsApp dan muat turun PDF di bawah kad resit.',
                'PDF resit guna template HTML sama seperti paparan web, saiz kertas thermal 80mm.',
                'Baiki kunci terjemahan ringkasan checkout untuk keadaan beautician dan jadual menunggu.',
            ],
        ],
    ],

    '4.11.59' => [
        'date' => '2026-08-28',
        'en' => [
            'summary' => 'Checkout treatment slots and appointment summaries on receipt, invoice, and email.',
            'changes' => [
                'Checkout: appointment time slot grid (portal-style), full-width stacked date/time fields, live appointment details in order summary sidebar.',
                'Checkout: no payment gateway auto-selected on load; customer must choose explicitly.',
                'Receipt, invoice, email, and PDF: per-treatment appointment details (branch, beautician, schedule) above payment totals via OrderTreatmentAppointmentSummary.',
            ],
        ],
        'ms' => [
            'summary' => 'Slot temujanji checkout dan butiran temujanji setiap rawatan pada resit, invois, dan e-mel.',
            'changes' => [
                'Checkout: grid slot masa (gaya portal), tarikh/masa susun penuh, butiran temujanji langsung di sidebar ringkasan order.',
                'Checkout: tiada gateway bayaran dipilih automatik; pelanggan mesti pilih sendiri.',
                'Resit, invois, e-mel, dan PDF: butiran temujanji setiap rawatan (cawangan, beautician, jadual) di atas jumlah bayaran.',
            ],
        ],
    ],

    '4.11.58' => [
        'date' => '2026-08-28',
        'en' => [
            'summary' => 'Polish checkout order summary item layout, remove button, and price alignment.',
            'changes' => [
                'Order summary: dedicated aside column so remove (×) no longer overlaps prices.',
                'Remove button styled as a prominent pink circle; larger strikethrough regular price text.',
                'Sale and regular line prices right-aligned in a fixed-width column with tabular numerals.',
            ],
        ],
        'ms' => [
            'summary' => 'Penggilapan susun atur item ringkasan order checkout, butang buang, dan penjajaran harga.',
            'changes' => [
                'Ringkasan order: lajur aside khusus supaya buang (×) tidak bertindih harga.',
                'Butang buang digaya bulat pink menonjol; teks harga asal coret lebih besar.',
                'Harga jualan dan asal dijajarkan kanan dalam lajur lebar tetap dengan angka tabular.',
            ],
        ],
    ],

    '4.11.57' => [
        'date' => '2026-08-28',
        'en' => [
            'summary' => 'Modern checkout treatment booking flow, order summary item removal, and BM translation fixes.',
            'changes' => [
                'Treatment checkout: explicit schedule mode (no default), branch → beautician → date/time prerequisite flow with focus/pulse hints and circle numbering per product line.',
                'Fix Errors.js showing single-letter validation messages; register checkout JS translation keys via js_langs partial.',
                'Order summary: clearer item dividers, remove item button (same as cart), sync treatment schedules and redirect when cart empties.',
                'Checkout UI: payment card bottom margin, softer field attention pulse on picker wrappers, full BM checkout strings.',
            ],
        ],
        'ms' => [
            'summary' => 'Aliran tempahan rawatan checkout moden, buang item ringkasan order, dan pembetulan terjemahan BM.',
            'changes' => [
                'Checkout rawatan: mod jadual eksplisit (tiada lalai), aliran prasyarat cawangan → beautician → tarikh/masa dengan fokus/pulse dan nombor bulat setiap produk.',
                'Betulkan Errors.js papar satu huruf mesej validasi; daftar kunci terjemahan JS checkout melalui partial js_langs.',
                'Ringkasan order: pemisah item lebih jelas, butang buang item (sama cart), sync jadual rawatan dan redirect bila cart kosong.',
                'UI checkout: margin bawah kad bayaran, pulse perhatian field pada wrapper picker, rentetan BM checkout penuh.',
            ],
        ],
    ],

    '4.11.56' => [
        'date' => '2026-08-28',
        'en' => [
            'summary' => 'Unify customer billing/shipping addresses, modernize account profile and addresses, and fix loyalty earn sync.',
            'changes' => [
                'Checkout and account: sync profile names with billing defaults, seed addresses from last order, and add separate billing/shipping defaults with shipping_address_id migration.',
                'My Addresses: checkout-style cards, billing vs shipping sections, set-default actions, and checkout prefill when shipping differs.',
                'Account profile: SaaS-style layout, shared DOB datepicker, remove stamp cards from profile (membership moved to loyalty page).',
                'Loyalty: fix points earn idempotency and order fillable, clearer transaction labels, membership sidebar on loyalty index.',
                'Header nav label My Account and bordered desktop account sidebar menu.',
            ],
        ],
        'ms' => [
            'summary' => 'Seragamkan alamat bil/penghantaran pelanggan, modenkan profil dan alamat akaun, dan betulkan sync mata ganjaran.',
            'changes' => [
                'Checkout dan akaun: selaraskan nama profil dengan lalai bil, seed alamat dari order terakhir, dan lalai bil/penghantaran berasingan dengan migrasi shipping_address_id.',
                'Alamat Saya: kad gaya checkout, bahagian bil vs penghantaran, tindakan tetapkan lalai, dan prefill checkout bila penghantaran berbeza.',
                'Profil akaun: susun atur SaaS, datepicker DOB berkongsi, buang kad setem dari profil (keahlian dipindah ke halaman ganjaran).',
                'Ganjaran: betulkan idempotency earn dan fillable order, label transaksi lebih jelas, sidebar keahlian di indeks loyalty.',
                'Label nav header Akaun Saya dan menu sidebar akaun desktop berbingkai.',
            ],
        ],
    ],

    '4.11.55' => [
        'date' => '2026-08-28',
        'en' => [
            'summary' => 'Portal beautician booking fixes, shared manual slot resolver, and Flatpickr locale/grid polish.',
            'changes' => [
                'Add ManualBookingSlotsResolver so admin and portal manual booking use the same DB-backed availability rules.',
                'Portal manual booking: validate product/spa branch, always send beautician_id for slot requests, and pass default spa branch from locked beautician.',
                'Flatpickr: shared locale helper, grid styling, and modern datepicker updates across admin and storefront.',
                'Portal UI: job sheet hero, account page, calendar, admin preview banner, and CRM toolbar polish; remove unused dashboard-hero partial.',
            ],
        ],
        'ms' => [
            'summary' => 'Pembetulan tempahan portal beautician, resolver slot manual berkongsi, dan penggilapan Flatpickr locale/grid.',
            'changes' => [
                'Tambah ManualBookingSlotsResolver supaya tempahan manual admin dan portal guna peraturan ketersediaan DB yang sama.',
                'Tempahan manual portal: sahkan produk/cawangan spa, hantar beautician_id untuk slot, dan lalai spa branch dari beautician dikunci.',
                'Flatpickr: helper locale berkongsi, gaya grid, dan kemas kini datepicker moden di admin dan storefront.',
                'UI portal: hero jobsheet, halaman akaun, kalendar, banner pratonton admin, dan penggilapan toolbar CRM; buang partial dashboard-hero tidak digunakan.',
            ],
        ],
    ],

    '4.11.54' => [
        'date' => '2026-08-28',
        'en' => [
            'summary' => 'Fix reports dashboard SQL error, and expand Operations and OneSender queue tooling.',
            'changes' => [
                'Fix beautician bookings report stats query (invalid SQL parameter binding on /admin/reports).',
                'Operations: diagnose why pending queue jobs are waiting (delayed, sync driver, scheduler, worker).',
                'Operations: run a one-off queue:work batch from admin and show suggested worker/cron commands.',
                'OneSender outbound queue: status count chips, refined filters, and bulk delete for filtered rows.',
            ],
        ],
        'ms' => [
            'summary' => 'Betulkan ralat SQL papan laporan, dan luaskan alat Operations serta antrian OneSender.',
            'changes' => [
                'Betulkan query statistik laporan tempahan beautician (parameter SQL tidak sah pada /admin/reports).',
                'Operations: diagnosis sebab job antrian tertunda (lewat, driver sync, penjadual, worker).',
                'Operations: jalankan batch queue:work sekali dari admin dan papar arahan worker/cron yang dicadangkan.',
                'Antrian keluar OneSender: cip kiraan status, penapis diperhalusi, dan padam pukal untuk baris ditapis.',
            ],
        ],
    ],

    '4.11.53' => [
        'date' => '2026-08-27',
        'en' => [
            'summary' => 'Jobsheet CRM polish, safer TBA start flow, and accurate admin sidebar breadcrumbs.',
            'changes' => [
                'Restyle Jobsheet page header to match Appointment Availability (eyebrow, icon, live status badge).',
                'Block Start treatment for TBA bookings; show Schedule Date First and open the schedule drawer (API guarded).',
                'Left-align KPI stats cards and keep CRM lead text full width.',
                'Global admin breadcrumb follows the sidebar trail (e.g. Appointments > Jobsheet / Availability).',
                'Fix sidebar dual-active bugs: Dashboard no longer prefixes every /admin/* route; leaf items match exact paths.',
                'Portal calendar page, CRM toolbars, and related Jobsheet/pipeline UX polish.',
            ],
        ],
        'ms' => [
            'summary' => 'Penggilap CRM Jobsheet, aliran mula TBA yang lebih selamat, dan breadcrumb sidebar admin yang tepat.',
            'changes' => [
                'Restyle header halaman Jobsheet supaya sepadan Appointment Availability (eyebrow, ikon, badge status langsung).',
                'Halang Start treatment untuk tempahan TBA; papar Schedule Date First dan buka drawer jadual (API digaul).',
                'Kad statistik KPI dijajar kiri dan teks lead CRM kekal full width.',
                'Breadcrumb admin global mengikut trail sidebar (cth. Appointments > Jobsheet / Availability).',
                'Betulkan bug dual-active sidebar: Dashboard tidak lagi prefix setiap /admin/*; item leaf match path tepat.',
                'Halaman kalendar portal, toolbar CRM, dan penggilap UX Jobsheet/pipeline berkaitan.',
            ],
        ],
    ],

    '4.11.52' => [
        'date' => '2026-08-27',
        'en' => [
            'summary' => 'Appointment availability product-variant tree and reliable branch schedule saves.',
            'changes' => [
                'Show every treatment variant under its parent product in the availability Treatment dropdown (searchable product tree).',
                'Fix Selectize search typing when a treatment is already selected.',
                'Fix branch/treatment schedule save 422 caused by Laravel distinct validating the same start times across different weekdays.',
            ],
        ],
        'ms' => [
            'summary' => 'Pokok produk–varian dalam appointment availability dan simpan jadual cawangan yang boleh dipercayai.',
            'changes' => [
                'Paparkan setiap varian rawatan di bawah produk induk dalam dropdown Treatment (pokok produk boleh dicari).',
                'Betulkan taip carian Selectize apabila rawatan sudah dipilih.',
                'Betulkan 422 simpan jadual cawangan/rawatan kerana distinct Laravel menyemak masa mula yang sama merentas hari berbeza.',
            ],
        ],
    ],

    '4.11.51' => [

        'date' => '2026-08-27',
        'en' => [
            'summary' => 'Reliable checkout thank-you pages, working payment-proof links, and safer availability editing.',
            'changes' => [
                'Fix checkout completion so the thank-you page still opens after bank transfer and Chip webhook races.',
                'Fix private payment-proof URLs under /fleetcart and locale redirects so signed links open instead of 403.',
                'Show customer payment proof in the admin order bank-transfer sidebar.',
                'Add beautician availability settings on the admin beautician edit page (shared with portal).',
                'Restore Quick times on appointment availability, focus invalid times for correction, and harden branch schedule save validation.',
            ],
        ],
        'ms' => [
            'summary' => 'Halaman thank-you checkout yang boleh dipercayai, pautan bukti bayaran yang berfungsi, dan suntingan ketersediaan yang lebih selamat.',
            'changes' => [
                'Betulkan penamat checkout supaya halaman thank-you masih dibuka selepas bank transfer dan perlumbaan webhook Chip.',
                'Betulkan URL bukti bayaran peribadi di bawah /fleetcart dan redirect locale supaya pautan bertandatangan dibuka, bukan 403.',
                'Paparkan bukti bayaran pelanggan dalam sidebar bank transfer pesanan admin.',
                'Tambah tetapan ketersediaan beautician pada halaman edit admin beautician (dikongsi dengan portal).',
                'Pulihkan Quick times pada appointment availability, fokuskan masa tidak sah untuk dibetulkan, dan perkukuh validasi simpan jadual cawangan.',
            ],
        ],
    ],

    '4.11.50' => [
        'date' => '2026-08-27',
        'en' => [
            'summary' => 'Category product heroes, accurate loyalty stamps, and clearer treatment portal tools.',
            'changes' => [
                'Show a Kosmetik-style category showcase hero on spa, aesthetic, and all future category product pages (en/ms).',
                'Loyalty stamp ticks now follow real stamp entry records instead of a denormalized counter.',
                'Always show My Availability in treatment portal Quick links (including admin preview).',
                'Fix appointment availability time picker format (g:00 AM) and remove confusing Quick times shortcuts.',
            ],
        ],
        'ms' => [
            'summary' => 'Hero kategori produk, setem loyalty yang tepat, dan alat portal rawatan yang lebih jelas.',
            'changes' => [
                'Paparkan hero showcase gaya Kosmetik pada halaman produk spa, aesthetic, dan semua kategori baharu (en/ms).',
                'Tick setem loyalty kini mengikut rekod entry sebenar, bukan counter yang dinormalisasi.',
                'Sentiasa paparkan My Availability dalam Quick links portal rawatan (termasuk pratonton admin).',
                'Betulkan format pemilih masa ketersediaan (g:00 AM) dan buang pintasan Quick times yang mengelirukan.',
            ],
        ],
    ],

    '4.11.49' => [
        'date' => '2026-08-27',
        'en' => [
            'summary' => 'Faster homepage load, fixed hero slider crash, and Shop by Category card layout.',
            'changes' => [
                'Fix homepage hero Uncaught ReferenceError: $ is not defined by reading slider options without jQuery.',
                'Split homepage JS so above-the-fold hero/features load first and below-fold sections load in parallel chunks.',
                'Fix Shop by Category tab switches that showed one giant product card; Swiper now updates instead of fighting Alpine.',
            ],
        ],
        'ms' => [
            'summary' => 'Homepage lebih pantas, betulkan ralat hero slider, dan layout card Shop by Category.',
            'changes' => [
                'Betulkan ralat homepage hero Uncaught ReferenceError: $ is not defined dengan baca pilihan slider tanpa jQuery.',
                'Pecahkan JS homepage supaya hero/features di atas load dulu dan section bawah load secara parallel.',
                'Betulkan tukar tab Shop by Category yang papar satu card produk besar; Swiper kini update tanpa bergaduh dengan Alpine.',
            ],
        ],
    ],

    '4.11.48' => [
        'date' => '2026-08-27',
        'en' => [
            'summary' => 'Settings hero headers, sidebar logo upload, and richer user edit Orders tab.',
            'changes' => [
                'Add shared settings-tab hero headers (WhatsApp green accent; other tabs use a blue/neutral hero).',
                'Support a dedicated admin sidebar logo upload, separate from login and mini logos, with modern media dropzones.',
                'WhatsApp Birthday Reminder: default birthday poster, live preview sync, and clearer reward-type panels.',
                'User edit: Orders tab (with spa branch and beautician), clearer required-field asterisks, prominent right-aligned Save, and tab validation error badges.',
                'Users index table columns are sortable for user, roles, loyalty, status, and last login.',
            ],
        ],
        'ms' => [
            'summary' => 'Header hero tetapan, muat naik logo sidebar, dan tab Pesanan pengguna yang lebih lengkap.',
            'changes' => [
                'Tambah header hero tab tetapan (aksen hijau WhatsApp; tab lain biru/neutral).',
                'Sokong muat naik logo sidebar admin berasingan daripada logo login dan mini, dengan dropzone media moden.',
                'Peringatan Hari Lahir WhatsApp: poster lalai, pratonton langsung, dan panel jenis ganjaran lebih jelas.',
                'Sunting pengguna: tab Pesanan (dengan cawangan spa dan beautician), asterisk medan wajib, butang Simpan lebih menonjol di kanan, dan lencana ralat validasi tab.',
                'Lajur jadual senarai pengguna boleh diisih untuk pengguna, peranan, loyaliti, status, dan log masuk terakhir.',
            ],
        ],
    ],

    '4.11.47' => [
        'date' => '2026-08-27',
        'en' => [
            'summary' => 'Professional WhatsApp settings with type-aware previews and cleaner SaaS media controls.',
            'changes' => [
                'Refresh WhatsApp notification sections with consistent two-column layouts, spacing, and sending controls.',
                'Add type-aware WhatsApp previews: text messages show text only, while image and PDF templates show their matching media.',
                'Move Birthday greeting image upload above the message template and refine the modern SaaS dropzone.',
                'Send the customer receipt PDF with new-order WhatsApp notifications.',
            ],
        ],
        'ms' => [
            'summary' => 'Tetapan WhatsApp profesional dengan pratonton mengikut jenis dan kawalan media gaya SaaS yang lebih kemas.',
            'changes' => [
                'Perbaharui section notifikasi WhatsApp dengan layout dua kolum, jarak, dan kawalan penghantaran yang seragam.',
                'Tambah pratonton mengikut jenis: mesej teks hanya papar teks, manakala templat imej dan PDF papar media yang sepadan.',
                'Pindahkan muat naik imej ucapan Birthday ke atas templat mesej dan perkemas dropzone gaya SaaS.',
                'Hantar PDF resit pelanggan bersama notifikasi WhatsApp pesanan baharu.',
            ],
        ],
    ],

    '4.11.46' => [
        'date' => '2026-08-27',
        'en' => [
            'summary' => 'WhatsApp Birthday Reminder: greet customers with image messages and loyalty rewards from Settings → WhatsApp.',
            'changes' => [
                'New WhatsappBirthdayReminder module: daily cron finds birthdays, awards points/discount/voucher, sends OneSender WhatsApp (image + caption).',
                'Configure under Admin → Settings → WhatsApp Notifications (tab=sms); delivery logs without a top-level sidebar item.',
                'Integrates with Loyalty (shared yearly birthday points reference; skips duplicate plain-text birthday WhatsApp).',
                'Live WhatsApp message template preview on the SMS settings tab.',
            ],
        ],
        'ms' => [
            'summary' => 'Peringatan Hari Lahir WhatsApp: ucapan bergambar dan ganjaran loyaliti dari Tetapan → WhatsApp.',
            'changes' => [
                'Modul WhatsappBirthdayReminder baharu: cron harian cari hari lahir, beri poin/diskaun/baucar, hantar WhatsApp OneSender (gambar + kapsyen).',
                'Konfigurasi di Admin → Tetapan → Notifikasi WhatsApp (tab=sms); log penghantaran tanpa item sidebar utama.',
                'Integrasi Loyalty (rujukan poin hari lahir tahunan sama; elak WhatsApp teks birthday berganda).',
                'Pratonton templat mesej WhatsApp secara langsung pada tab tetapan SMS.',
            ],
        ],
    ],

    '4.11.45' => [
        'date' => '2026-08-26',
        'en' => [
            'summary' => 'Align order vs payment statuses, improve Orders filters, and POS-style Total/Net sales by branch.',
            'changes' => [
                'Order status slimmed to Pending/Processing/Completed/Canceled; Refunded moved to payment status.',
                'Orders index: Offline/Online channel filters, Flatpickr date range, month and search filters.',
                'Bank-transfer payment verification (transaction ID) and Transactions Offline/Online tabs.',
                'Dashboard Total Sales = booked (non-canceled); Net Sales = Paid only; sales by spa branch.',
                'Faster status updates via database queue driver and lighter booking observer on status-only changes.',
            ],
        ],
        'ms' => [
            'summary' => 'Seragamkan status pesanan vs bayaran, penapis Orders lebih baik, dan jualan Total/Net gaya POS mengikut cawangan.',
            'changes' => [
                'Status pesanan diperkecil kepada Menunggu/Diproses/Selesai/Dibatalkan; Dipulangkan dipindah ke status bayaran.',
                'Indeks Orders: penapis saluran Luar/Dalam talian, julat tarikh Flatpickr, bulan dan carian.',
                'Pengesahan bayaran bank transfer (ID transaksi) dan tab Offline/Online pada Transactions.',
                'Dashboard Jumlah Jualan = ditempah (tidak dibatalkan); Jualan Bersih = Dibayar sahaja; pecahan mengikut cawangan spa.',
                'Kemas kini status lebih pantas dengan queue database dan observer booking lebih ringan.',
            ],
        ],
    ],

    '4.11.38' => [
        'date' => '2026-08-26',
        'en' => [
            'summary' => 'Fix loyalty points not deducting from wallet after checkout redemption.',
            'changes' => [
                'Persist loyalty_discount_amount correctly (fillable typo).',
                'Debit reward points from the order snapshot so ClearCart cannot skip redemption.',
                'Add loyalty:repair-redemptions to backfill missing ledger debits for past orders.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki mata loyalty yang tidak ditolak dari wallet selepas tebus di checkout.',
            'changes' => [
                'Simpan loyalty_discount_amount dengan betul (typo fillable).',
                'Debit mata ganjaran dari snapshot pesanan supaya ClearCart tidak langkau penebusan.',
                'Tambah loyalty:repair-redemptions untuk backfill debit ledger yang hilang.',
            ],
        ],
    ],

    '4.11.37' => [
        'date' => '2026-08-26',
        'en' => [
            'summary' => 'Bank-transfer checkout complete shows Pending hero, 2-column booking details, and Print invoice.',
            'changes' => [
                'Pending clock hero and copy when bank transfer payment is awaiting verification.',
                'Treatment booking details use a two-column layout including appointment date and time.',
                'Add Print invoice action that opens the printable invoice with auto-print.',
            ],
        ],
        'ms' => [
            'summary' => 'Checkout complete bank transfer papar hero Pending, butiran tempahan 2 kolum, dan Cetak invois.',
            'changes' => [
                'Hero jam Pending dan teks bila bayaran bank transfer menunggu pengesahan.',
                'Butiran tempahan rawatan dalam layout dua kolum termasuk tarikh dan masa.',
                'Tambah tindakan Cetak invois yang buka invois boleh cetak dengan auto-print.',
            ],
        ],
    ],

    '4.11.36' => [
        'date' => '2026-08-26',
        'en' => [
            'summary' => 'Polish checkout complete: hide expired stamp cards, clearer bank-transfer pending, stronger CTAs.',
            'changes' => [
                'Order complete rewards use forOrderComplete and exclude expired stamp wallets.',
                'Add bank-transfer pending helper copy after proof upload.',
                'Improve action hierarchy, expired stamp styling, and remove emoji from Rewards heading.',
            ],
        ],
        'ms' => [
            'summary' => 'Perhalusi checkout complete: sembunyi kad setem luput, pending bank transfer lebih jelas, CTA lebih kuat.',
            'changes' => [
                'Ganjaran order complete guna forOrderComplete dan kecualikan wallet setem luput.',
                'Tambah teks bantuan pending bank transfer selepas muat naik bukti.',
                'Perbaiki hierarki tindakan, gaya setem luput, dan buang emoji dari tajuk Rewards.',
            ],
        ],
    ],

    '4.11.35' => [
        'date' => '2026-08-26',
        'en' => [
            'summary' => 'Fix checkout payment method resetting to FPX when cart totals update.',
            'changes' => [
                'Stop Alpine cart effects from overwriting the customer\'s selected payment gateway.',
                'Default payment only when empty or when the selected method is no longer available.',
                'Snapshot payment_method at place-order so bank transfer and other gateways stay faithful through submit.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki kaedah bayaran checkout yang reset ke FPX bila jumlah cart dikemas kini.',
            'changes' => [
                'Hentikan Alpine cart effect daripada menimpa gateway bayaran yang dipilih pelanggan.',
                'Default kaedah bayaran hanya bila kosong atau kaedah dipilih tiada lagi.',
                'Snapshot payment_method semasa place-order supaya bank transfer dan gateway lain kekal tepat hingga submit.',
            ],
        ],
    ],

    '4.11.34' => [
        'date' => '2026-08-26',
        'en' => [
            'summary' => 'Bank transfer proof upload is now a SaaS-style drag-and-drop dropzone with live preview.',
            'changes' => [
                'Support drag and drop, browse, replace, and remove for payment proof files.',
                'Show image thumbnails instantly and PDF file cards with size metadata.',
                'Validate type and 10 MB size client-side with clear error feedback.',
            ],
        ],
        'ms' => [
            'summary' => 'Muat naik bukti bank transfer kini dropzone seret-lepas gaya SaaS dengan pratonton langsung.',
            'changes' => [
                'Sokong seret & lepas, browse, ganti, dan buang untuk fail bukti bayaran.',
                'Paparkan thumbnail imej serta-merta dan kad fail PDF dengan saiz.',
                'Sahkan jenis dan saiz 10 MB di sisi klien dengan maklum balas ralat yang jelas.',
            ],
        ],
    ],

    '4.11.33' => [
        'date' => '2026-08-26',
        'en' => [
            'summary' => 'Polish bank transfer checkout: checkmark selection, clearer instructions, modern proof upload.',
            'changes' => [
                'Payment method radios use the same pink checkmark control as schedule and terms.',
                'Hide duplicate bank-transfer description once instructions expand; show a soft instructions card.',
                'Replace the native file input with a branded upload dropzone and file-selected feedback.',
            ],
        ],
        'ms' => [
            'summary' => 'Perhalusi bank transfer checkout: pilihan checkmark, arahan lebih jelas, muat naik bukti moden.',
            'changes' => [
                'Radio kaedah bayaran guna kawalan checkmark pink sama seperti schedule dan terms.',
                'Sembunyikan deskripsi bank transfer berganda bila arahan dibuka; papar kad arahan lembut.',
                'Ganti input fail native dengan dropzone muat naik berjenama dan maklum balas fail dipilih.',
            ],
        ],
    ],

    '4.11.32' => [
        'date' => '2026-08-26',
        'en' => [
            'summary' => 'Polish checkout terms consent with a visible checkmark card and clearer BM copy.',
            'changes' => [
                'Replace the invisible pink terms checkbox with a soft consent card and white la-check tick.',
                'Enlarge the terms touch target, add focus-visible and reduced-motion support.',
                'Fix Bahasa Malaysia strings for agree-to-terms and Place Order Now.',
            ],
        ],
        'ms' => [
            'summary' => 'Perhalusi persetujuan terms checkout dengan kad checkmark yang nampak dan salinan BM lebih jelas.',
            'changes' => [
                'Ganti checkbox terms pink yang tak nampak dengan kad consent lembut dan tick la-check putih.',
                'Besarkan sasaran sentuh terms, tambah focus-visible dan sokongan reduced-motion.',
                'Betulkan rentetan BM untuk bersetuju terms dan Buat Pesanan Sekarang.',
            ],
        ],
    ],

    '4.11.31' => [
        'date' => '2026-08-26',
        'en' => [
            'summary' => 'Checkout schedule and terms selections use checkmarks; fix Recommended badge overlap.',
            'changes' => [
                'Replace schedule TBA/now radio dots with pink checkmark boxes.',
                'Show a white tick on the Terms checkbox when checked (was invisible on pink fill).',
                'Keep the Recommended address badge in-flow so it no longer floats over the Account link.',
            ],
        ],
        'ms' => [
            'summary' => 'Pilihan schedule dan terms checkout guna checkmark; baiki overlap badge Recommended.',
            'changes' => [
                'Ganti titik radio TBA/sekarang dengan kotak checkmark pink.',
                'Paparkan tick putih pada checkbox Terms bila dipilih (dahulu tak nampak atas fill pink).',
                'Kekalkan badge Recommended alamat dalam aliran supaya tidak terapung atas pautan Account.',
            ],
        ],
    ],

    '4.11.30' => [
        'date' => '2026-08-26',
        'en' => [
            'summary' => 'Fix checkout availability dates 422 caused by UTC date off-by-one.',
            'changes' => [
                'Format appointment from/to dates with local calendar day instead of toISOString().',
                'Clamp past from dates to today on the availability/dates API instead of rejecting with 422.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki 422 tarikh availability checkout akibat off-by-one UTC.',
            'changes' => [
                'Format tarikh from/to temujanji dengan hari kalendar tempatan, bukan toISOString().',
                'Clamp tarikh from yang lalu kepada hari ini pada API availability/dates dan bukan tolak dengan 422.',
            ],
        ],
    ],

    '4.11.29' => [
        'date' => '2026-08-26',
        'en' => [
            'summary' => 'Ignore empty CSP violation reports that were spamming the security log.',
            'changes' => [
                'Skip logging CSP report payloads with no document URI, blocked URI, directive, or source file.',
            ],
        ],
        'ms' => [
            'summary' => 'Abaikan laporan CSP kosong yang spam log keselamatan.',
            'changes' => [
                'Langkau log payload laporan CSP tanpa document URI, blocked URI, directive, atau source file.',
            ],
        ],
    ],

    '4.11.28' => [
        'date' => '2026-08-26',
        'en' => [
            'summary' => 'Stop queue workers from seeding Google Sheets settings when MySQL is down.',
            'changes' => [
                'Skip Google Sheets applyMissingOnly during console/queue boots to avoid Connection refused spam.',
                'Probe DB reachability before writing missing Google Sheets status settings.',
                'Return an empty settings collection when cache and MySQL both fail so boot can continue.',
            ],
        ],
        'ms' => [
            'summary' => 'Hentikan queue worker seed setting Google Sheets bila MySQL down.',
            'changes' => [
                'Langkau Google Sheets applyMissingOnly pada boot console/queue bagi elak spam Connection refused.',
                'Periksa DB reachable sebelum tulis setting status Google Sheets yang hilang.',
                'Pulangkan koleksi settings kosong bila cache dan MySQL gagal supaya boot boleh terus.',
            ],
        ],
    ],

    '4.11.27' => [
        'date' => '2026-08-26',
        'en' => [
            'summary' => 'Stop OneSender from using seeded placeholder beautician phones; fix Operations console in-page anchors.',
            'changes' => [
                'Remove hardcoded 60100000001/02 seeder phones; WhatsApp recipients must be set from Admin.',
                'Migration clears existing placeholder beautician phones so notifications skip until a real number is saved.',
                'Operations jump/issue links use the admin operations route so #fragments work with the layout base href.',
            ],
        ],
        'ms' => [
            'summary' => 'Hentikan OneSender guna nombor placeholder dari seeder; baiki pautan anchor konsol Operations.',
            'changes' => [
                'Buang nombor hardcoded 60100000001/02 dari seeder; penerima WhatsApp mesti diset dari Admin.',
                'Migration kosongkan telefon placeholder beautician sedia ada supaya notifikasi dilangkau sehingga nombor sebenar disimpan.',
                'Pautan jump/isu Operations guna route admin supaya #fragment berfungsi dengan base href layout.',
            ],
        ],
    ],

    '4.11.26' => [
        'date' => '2026-08-26',
        'en' => [
            'summary' => 'Stop CacheHealth ALERT spam on artisan cron and soft-fail schedule commands during MySQL outages.',
            'changes' => [
                'Skip filesystem cache put/forget probes on CLI boots to avoid concurrent cli-data race ALERTs.',
                'Swallow Flysystem warnings instead of converting them into ErrorException/CachePoolException.',
                'Soft-fail operations:heartbeat and onesender:process-outbound-queue when MySQL is unreachable.',
            ],
        ],
        'ms' => [
            'summary' => 'Hentikan spam ALERT CacheHealth pada cron artisan dan soft-fail arahan schedule semasa MySQL down.',
            'changes' => [
                'Langkau probe put/forget cache fail pada boot CLI bagi elak race cli-data yang cetus ALERT.',
                'Telan amaran Flysystem tanpa tukar kepada ErrorException/CachePoolException.',
                'Soft-fail operations:heartbeat dan onesender:process-outbound-queue bila MySQL tidak boleh dihubungi.',
            ],
        ],
    ],

    '4.11.25' => [
        'date' => '2026-08-26',
        'en' => [
            'summary' => 'Queue order notification, loyalty, and booking sync listeners so checkout/status updates stay responsive.',
            'changes' => [
                'Move WhatsApp, email, and SMS order listeners to ShouldQueueAfterCommit with retries.',
                'Queue loyalty earn/stamp and treatment booking sync after commit; keep cart loyalty redemption synchronous.',
                'Fix booking status mapping so cancel/refund still applies when the listener runs from the queue.',
            ],
        ],
        'ms' => [
            'summary' => 'Queue listener notifikasi order, loyalty, dan sync booking supaya checkout/kemaskini status kekal responsif.',
            'changes' => [
                'Pindahkan listener WhatsApp, e-mel, dan SMS order ke ShouldQueueAfterCommit dengan retry.',
                'Queue earn/stamp loyalty dan sync treatment booking selepas commit; kekalkan redemption loyalty dari cart secara sync.',
                'Betulkan pemetaan status booking supaya cancel/refund masih diguna bila listener berjalan dari queue.',
            ],
        ],
    ],
    '4.11.24' => [
        'date' => '2026-08-25',
        'en' => [
            'summary' => 'Hardened boot so artisan and the app survive MySQL or file-cache outages.',
            'changes' => [
                'Skip Google Sheets settings seeding when the database is unreachable instead of crashing provider boot.',
                'Make CacheHealth tag probes fail soft: suppress corrupt filesystem-tag warnings, purge bad tag files, and fall back to array cache.',
            ],
        ],
        'ms' => [
            'summary' => 'Kukuhkan boot supaya artisan dan aplikasi tahan bila MySQL atau file cache bermasalah.',
            'changes' => [
                'Langkau seed settings Google Sheets apabila pangkalan data tidak boleh dihubungi supaya boot provider tidak meletup.',
                'Jadikan probe tag CacheHealth fail-soft: sekatan amaran tag filesystem rosak, padam fail tag buruk, dan fallback ke cache array.',
            ],
        ],
    ],
    '4.11.23' => [
        'date' => '2026-08-25',
        'en' => [
            'summary' => 'Fixed SweetAlert2 success and status icons that rendered as broken checkmarks.',
            'changes' => [
                'Stop forcing fixed-pixel SweetAlert icon sizes that break em-based success/error mark geometry.',
                'Scale icons with SweetAlert2 --swal2-icon-zoom and rebuild admin/storefront assets.',
            ],
        ],
        'ms' => [
            'summary' => 'Betulkan ikon SweetAlert2 success dan status yang papar sebagai checkmark rosak.',
            'changes' => [
                'Hentikan override saiz ikon SweetAlert dalam piksel tetap yang merosakkan geometri tanda success/error berasaskan em.',
                'Skalakan ikon dengan --swal2-icon-zoom SweetAlert2 dan bina semula aset admin/storefront.',
            ],
        ],
    ],
    '4.11.22' => [
        'date' => '2026-08-25',
        'en' => [
            'summary' => 'Modern Operations health console with clearer capacity and security-aware presentation.',
            'changes' => [
                'Refresh admin Operations visuals: sticky glass jump nav, live health banner, KPI capacity meters, and denser tables.',
                'Add secure-note copy and tighter form labels while keeping CSRF, escaped output, and permission-gated actions.',
            ],
        ],
        'ms' => [
            'summary' => 'Konsol Operations yang lebih moden dengan kapasiti jelas dan pembentangan mesra keselamatan.',
            'changes' => [
                'Kemaskini visual Operations admin: jump nav glass melekat, banner kesihatan langsung, meter kapasiti KPI, dan jadual lebih padat.',
                'Tambah nota keselamatan dan label borang yang lebih kemas sambil mengekalkan CSRF, output escaped, dan tindakan berasaskan permission.',
            ],
        ],
    ],
    '4.11.21' => [
        'date' => '2026-08-25',
        'en' => [
            'summary' => 'Clearer Operations health console, friendlier storefront errors, and fixed consultation access lookup.',
            'changes' => [
                'Improve admin Operations with section jump links, actionable health issues, refresh timestamp, and clearer queue metrics.',
                'Show branded friendly error pages to browsers unless detailed errors are explicitly enabled for debugging.',
                'Fix consultation access lookup when only a hashed public token is stored.',
            ],
        ],
        'ms' => [
            'summary' => 'Konsol Operations lebih jelas, halaman ralat mesra, dan pembetulan lookup akses konsultasi.',
            'changes' => [
                'Perbaiki Operations admin dengan pautan bahagian, isu kesihatan yang boleh diambil tindakan, masa muat semula, dan metrik queue yang lebih jelas.',
                'Paparkan halaman ralat berjenama kepada pelayar kecuali ralat terperinci diaktifkan untuk debugging.',
                'Betulkan lookup akses konsultasi apabila hanya token awam yang di-hash disimpan.',
            ],
        ],
    ],
    '4.11.20' => [
        'date' => '2026-08-25',
        'en' => [
            'summary' => 'Calendar history visibility, working Profile drawer on every calendar surface, and portal CRM profile privacy hardening.',
            'changes' => [
                'Show completed appointments on month/week calendars even when the linked order was soft-deleted or removed, and allow their detail drawers to open.',
                'Wire the CRM customer profile drawer on admin Calendar and beautician job sheet so Profile always works.',
                'Scope portal customer profiles to the signed-in beautician and hide admin-only account links from portal responses.',
            ],
        ],
        'ms' => [
            'summary' => 'Paparan sejarah kalendar, drawer Profile pada semua kalendar, dan pengukuhan privasi profil CRM portal.',
            'changes' => [
                'Paparkan temujanji completed pada kalendar bulan/minggu walaupun order berkaitan soft-delete atau dipadam, dan benarkan drawer butiran dibuka.',
                'Sambungkan drawer profil pelanggan CRM pada Calendar admin dan job sheet beautician supaya Profile sentiasa berfungsi.',
                'Hadkan profil pelanggan portal kepada beautician yang log masuk dan sembunyikan pautan akaun admin daripada respons portal.',
            ],
        ],
    ],

    '4.11.19' => [
        'date' => '2026-08-25',
        'en' => [
            'summary' => 'Unified month and week calendars with correct local dates across admin, CRM agenda, and beautician portal.',
            'changes' => [
                'Fix calendar date shifting caused by UTC toISOString so appointments land on the correct day in Malaysia time.',
                'Enable Month and Week views on every calendar surface (admin calendar, CRM agenda, beautician portal) with shared controls.',
                'Respect spa branch filters in calendar events and place the Month/Week toggle beside Today on the right.',
            ],
        ],
        'ms' => [
            'summary' => 'Kalendar bulan dan minggu seragam dengan tarikh tempatan yang betul merentas admin, agenda CRM, dan portal beautician.',
            'changes' => [
                'Betulkan anjakan tarikh kalendar akibat UTC toISOString supaya temujanji masuk pada hari yang betul mengikut masa Malaysia.',
                'Dayakan paparan Bulan dan Minggu pada semua kalendar (admin, agenda CRM, portal beautician) dengan kawalan yang sama.',
                'Hormati penapis cawangan spa dalam event kalendar dan letakkan toggle Bulan/Minggu di kanan sebelah Hari ini.',
            ],
        ],
    ],

    '4.11.18' => [
        'date' => '2026-08-25',
        'en' => [
            'summary' => 'CRM Needs attention queues, orphan booking cleanup, portal ownership hardening, and faster dashboard loads.',
            'changes' => [
                'Replace booking-statistics charts on the CRM dashboard with actionable Needs attention buckets (overdue, unassigned, TBA, reminders, unpaid).',
                'Hide orphan checkout bookings after order deletion and soft-delete linked treatment rows when an order is removed.',
                'Scope beautician portal CRM, calendar, kanban, and customer profile lookups to the signed-in specialist.',
                'Cut CRM dashboard query load by skipping unused analytics/upcoming payloads and lightening ledger serialization.',
            ],
        ],
        'ms' => [
            'summary' => 'Giliran Perlu perhatian CRM, pembersihan tempahan orphan, pengukuhan skop portal, dan dashboard lebih pantas.',
            'changes' => [
                'Ganti carta statistik tempahan pada dashboard CRM dengan bucket tindakan Perlu perhatian (tertinggal, tiada pakar, TBA, peringatan, belum bayar).',
                'Sembunyikan tempahan checkout orphan selepas padam order dan soft-delete baris rawatan berkaitan apabila order dipadam.',
                'Hadkan CRM/kalendar/kanban/profil pelanggan portal beautician kepada pakar yang log masuk.',
                'Kurangkan query dashboard CRM dengan menyingkir payload analytics/upcoming yang tidak digunakan dan meringankan serialisasi ledger.',
            ],
        ],
    ],

    '4.11.17' => [
        'date' => '2026-08-25',
        'en' => [
            'summary' => 'CRM appointment drawer and operational pipeline UX polish for clearer customer context and faster day-to-day clinic actions.',
            'changes' => [
                'Fix stacked CRM drawers so Profile closes Appointment details and opens the customer profile on top.',
                'Polish the CRM customer profile drawer with clearer section headers, blue count badges, larger avatar/photo support, and appointment cards that open details.',
                'Improve calendar date selection by autofocusing the agenda panel for the selected day.',
                'Clarify booking source labels (Web checkout / Admin booked / Portal booked) and unify pipeline cards with aligned footers, side-by-side action buttons, and consistent visit/membership chips.',
            ],
        ],
        'ms' => [
            'summary' => 'Penambahbaikan UX drawer CRM dan pipeline operasi untuk konteks pelanggan lebih jelas serta tindakan klinik harian yang lebih pantas.',
            'changes' => [
                'Betulkan drawer CRM bertindih supaya Profile menutup Appointment details dan membuka profil pelanggan di atas.',
                'Perkemas drawer profil pelanggan CRM dengan tajuk bahagian lebih jelas, lencana kiraan biru, avatar/foto lebih besar, dan kad temujanji yang membuka butiran.',
                'Tingkatkan pemilihan tarikh kalendar dengan autofokus ke panel agenda untuk hari yang dipilih.',
                'Jelaskan label sumber tempahan (Checkout website / Tempahan admin / Tempahan portal) dan seragamkan kad pipeline dengan footer sejajar, butang tindakan sebelah-menyebelah, serta chip lawatan/keahlian yang konsisten.',
            ],
        ],
    ],
    '4.11.16' => [
        'date' => '2026-08-24',
        'en' => [
            'summary' => 'Production-hardened multi-treatment appointment management across customer, admin, beautician, and Google integrations.',
            'changes' => [
                'Restructure customer orders and My Appointments around multiple independently scheduled treatments, selected product options, modern availability calendars, and per-treatment beautician notes.',
                'Upgrade the admin and beautician appointment drawer with permission-aware work-log CRUD, treatment checklists, customer notes, activity history, schedule context, and responsive action layouts.',
                'Keep appointment changes synchronized with Google Calendar and Google Sheets using stable event identifiers, configurable sheet columns, and spreadsheet-formula injection protection.',
                'Harden booking scope, portal permissions, availability checks, migrations, and regression coverage for the complete multi-treatment workflow.',
            ],
        ],
        'ms' => [
            'summary' => 'Pengurusan temujanji berbilang rawatan yang diperkukuh untuk production merentasi pelanggan, admin, beautician, dan integrasi Google.',
            'changes' => [
                'Susun semula pesanan pelanggan dan My Appointments untuk pelbagai rawatan yang dijadualkan secara berasingan, pilihan produk pelanggan, kalendar availability moden, dan nota beautician bagi setiap rawatan.',
                'Naik taraf drawer temujanji admin dan beautician dengan CRUD work log berasaskan permission, checklist rawatan, nota pelanggan, sejarah aktiviti, konteks jadual, dan susun atur tindakan responsif.',
                'Selaraskan perubahan temujanji dengan Google Calendar dan Google Sheets menggunakan ID acara yang stabil, kolum sheet boleh dikonfigurasi, dan perlindungan suntikan formula spreadsheet.',
                'Perkukuh skop tempahan, permission portal, semakan availability, migrasi, dan regression test untuk keseluruhan aliran berbilang rawatan.',
            ],
        ],
    ],
    '4.11.15' => [
        'date' => '2026-08-24',
        'en' => [
            'summary' => 'Larger, more readable SweetAlert2 centered modals with bigger font, wider popup, and rebuilt production assets.',
            'changes' => [
                'Increase SweetAlert2 centered modal width to 720px with 36px title, 24px body text, larger icon, and expanded padding for better readability.',
                'Rebuild storefront and admin production assets (Vite) to compile updated SweetNotification styles and JS.',
                'Keep corner toast layout unchanged; only centered modal (success / confirm / error / info) is enlarged.',
            ],
        ],
        'ms' => [
            'summary' => 'Modal SweetAlert2 berpusat lebih besar dan mudah dibaca dengan fon lebih besar, popup lebih lebar, dan aset production dibina semula.',
            'changes' => [
                'Tingkatkan lebar modal SweetAlert2 berpusat ke 720px dengan tajuk 36px, teks badan 24px, ikon lebih besar, dan padding yang lebih luas.',
                'Bina semula aset production storefront dan admin (Vite) untuk menyusun gaya dan JS SweetNotification yang dikemas kini.',
                'Kekalkan susun atur toast sudut; hanya modal berpusat (kejayaan / sahkan / ralat / maklumat) yang dibesarkan.',
            ],
        ],
    ],
    '4.11.14' => [
        'date' => '2026-08-23 16:23',
        'en' => [
            'summary' => 'Enterprise appointment rescheduling for beauticians, availability-safe calendars, and complete order rewards context.',
            'changes' => [
                'Add a dedicated reschedule workspace in the admin and beautician portals with every treatment from the order, product thumbnails, booking details, and direct job-sheet calendar access.',
                'Restrict selectable dates and times to live branch, treatment, duration, beautician availability, capacity, and existing-booking rules; prevent stale cached availability and fail closed when availability cannot be verified.',
                'Save rescheduled appointments through validated services, keep customer orders and calendars synchronized, and queue optional WhatsApp notifications for customers and beauticians.',
                'Expand the admin order workspace with clearer treatment appointments, promotional pricing, loyalty points and stamp audit details, payment context, and responsive enterprise CRM presentation.',
            ],
        ],
        'ms' => [
            'summary' => 'Penjadualan semula temujanji enterprise untuk beautician, kalendar berasaskan availability, dan konteks ganjaran pesanan yang lengkap.',
            'changes' => [
                'Tambah ruang kerja reschedule khusus dalam portal admin dan beautician dengan semua rawatan dalam pesanan, thumbnail produk, maklumat tempahan, serta akses terus ke kalendar job sheet.',
                'Hadkan tarikh dan masa yang boleh dipilih kepada peraturan langsung cawangan, rawatan, tempoh, availability beautician, kapasiti, dan tempahan sedia ada; elakkan cache availability lapuk dan tutup pemilihan apabila availability tidak dapat disahkan.',
                'Simpan temujanji yang dijadualkan semula melalui servis tervalidasi, selaraskan pesanan pelanggan dan kalendar, serta queue notifikasi WhatsApp pilihan untuk pelanggan dan beautician.',
                'Lengkapkan ruang kerja pesanan admin dengan maklumat temujanji rawatan, harga promosi, audit mata loyalty dan stamp, konteks bayaran, serta paparan CRM enterprise responsif yang lebih jelas.',
            ],
        ],
    ],
    '4.11.13' => [
        'date' => '2026-08-23 13:03',
        'en' => [
            'summary' => 'Account order and reviews UI polish, fuller order pricing breakdown, review loyalty rewards, and friendlier rate-limit errors.',
            'changes' => [
                'Refresh the customer account order detail and reviews pages with clearer payment, rewards, and mobile layout.',
                'Show complete admin/storefront payment summary lines (shipping, tax, discount, loyalty, fees) even when values are zero, and surface loyalty redemption gaps reliably.',
                'Add loyalty points rewards for product reviews with admin settings, order-complete messaging, and coverage tests.',
                'Improve throttling and security rate-limit error pages with clearer customer-facing copy.',
            ],
        ],
        'ms' => [
            'summary' => 'Pengemaskinian UI pesanan akaun & ulasan, ringkasan harga lebih lengkap, ganjaran ulasan loyalty, dan ralat had kadar yang lebih mesra.',
            'changes' => [
                'Kemaskini halaman butiran pesanan akaun pelanggan dan ulasan dengan paparan bayaran, ganjaran, serta susun atur mudah alih yang lebih jelas.',
                'Paparkan baris ringkasan bayaran lengkap (penghantaran, cukai, diskaun, loyalty, yuran) walaupun nilai sifar, dan paparkan jurang penebusan mata dengan lebih tepat.',
                'Tambah ganjaran mata loyalty untuk ulasan produk dengan tetapan admin, mesej order-complete, dan ujian liputan.',
                'Perbaiki halaman ralat throttling / had kadar keselamatan dengan teks yang lebih mesra pelanggan.',
            ],
        ],
    ],
    '4.11.12' => [
        'date' => '2026-08-23 12:53',
        'en' => [
            'summary' => 'Enterprise CRM order workspace with responsive operations, customer context, and reliable section navigation.',
            'changes' => [
                'Redesign Admin Order Detail as a focused two-column CRM workspace with customer identity, lifecycle statuses, order total, and operational snapshot.',
                'Improve ordered items, payment summary, appointment, address, activity, and workflow control cards for faster order handling across desktop and mobile.',
                'Add accessible workspace navigation with active states, reduced-motion support, and reliable section scrolling that remains on the admin order URL.',
                'Rebuild the Order admin CSS and JavaScript production assets.',
            ],
        ],
        'ms' => [
            'summary' => 'Ruang kerja pesanan CRM enterprise dengan operasi responsif, konteks pelanggan, dan navigasi seksyen yang stabil.',
            'changes' => [
                'Reka semula Butiran Pesanan Admin sebagai ruang kerja CRM dua kolum dengan identiti pelanggan, status kitar hayat, jumlah pesanan, dan ringkasan operasi.',
                'Perkemas kad item pesanan, ringkasan bayaran, temujanji, alamat, aktiviti, dan kawalan aliran kerja untuk pengendalian lebih pantas di desktop dan mudah alih.',
                'Tambah navigasi ruang kerja yang boleh diakses dengan status aktif, sokongan reduced-motion, dan scroll seksyen yang kekal pada URL pesanan admin.',
                'Bina semula aset production CSS dan JavaScript admin Order.',
            ],
        ],
    ],
    '4.11.11' => [
        'date' => '2026-08-22 20:45',
        'en' => [
            'summary' => 'Multi-treatment checkout, deferred bookings until payment, 12-hour times, and checkout hardening.',
            'changes' => [
                'Support multiple treatment bookings per order with per-line schedule UI, slot validation, sibling overlap checks, and 30-minute checkout slot holds during online payment.',
                'Defer treatment booking sync until payment succeeds; re-validate slots on finalize; release holds only after bookings are created.',
                'Standardize appointment times to 12-hour AM/PM across admin, storefront, account orders, and thank-you pages (including multi-appointment display).',
                'Fix payment cancel cleanup, capture loyalty redemption on order placed, offline finalize rollback, and hourly stale pending_payment order expiry.',
            ],
        ],
        'ms' => [
            'summary' => 'Checkout multi-rawatan, tempahan ditangguh sehingga bayaran, masa 12 jam, dan pengukuhan checkout.',
            'changes' => [
                'Sokong beberapa tempahan rawatan setiap pesanan dengan UI jadual per baris, validasi slot, semak pertindihan sibling, dan hold slot checkout 30 minit semasa bayaran dalam talian.',
                'Tangguh sync tempahan sehingga bayaran berjaya; validasi semula slot semasa finalize; lepaskan hold hanya selepas tempahan dicipta.',
                'Seragamkan masa temujanji format 12 jam AM/PM di admin, storefront, pesanan akaun, dan halaman thank-you (termasuk paparan multi-temujanji).',
                'Baiki cleanup batal bayaran, tangkap redemption loyalty bila pesanan dibuat, rollback finalize offline, dan tamat tempoh pesanan pending_payment stale setiap jam.',
            ],
        ],
    ],
    '4.11.10' => [
        'date' => '2026-08-22 10:23',
        'en' => [
            'summary' => 'Faster, more reliable Sign in from beautician registration to admin login.',
            'changes' => [
                'Stop the storefront PWA service worker from intercepting /admin/* navigations so Sign in opens admin login without SW delays or intermittent 404/offline fallbacks.',
                'Harden legacy /{locale}/admin redirects to keep the install subdirectory (e.g. /fleetcart) so localized admin URLs never land on a domain-root 404.',
                'Remove the blocked Font Awesome CDN from beautician registration (inline SVG icons instead) and prefetch the admin login document for snappier navigation.',
            ],
        ],
        'ms' => [
            'summary' => 'Sign in dari pendaftaran beautician ke admin login lebih pantas dan lebih dipercayai.',
            'changes' => [
                'Hentikan service worker PWA storefront daripada memintas navigasi /admin/* supaya Sign in membuka admin login tanpa kelewatan SW atau fallback 404/offline sekali-sekala.',
                'Perkukuh redirect legacy /{locale}/admin supaya subdirectory install (cth. /fleetcart) kekal, agar URL admin berlocale tidak jatuh ke 404 di root domain.',
                'Buang CDN Font Awesome yang dihalang CSP daripada pendaftaran beautician (ganti ikon SVG sebaris) dan prefetch dokumen admin login untuk navigasi lebih pantas.',
            ],
        ],
    ],
    '4.11.9' => [
        'date' => '2026-08-22 10:02',
        'en' => [
            'summary' => 'Faster storefront loads, self-hosted fonts, stricter CSP, and safer social-login settings.',
            'changes' => [
                'Speed up storefront pages by lazy-loading phone input, datepicker, SweetAlert2, product zoom, and lightbox only when needed, and by removing unused jQuery from the global bundle.',
                'Self-host display fonts with @fontsource (latin 300/400/500) and remove Google Fonts requests from the storefront and auth layouts.',
                'Enforce Content-Security-Policy by default, sanitize custom header/footer assets to an HTTPS script allowlist, escape social-login related XSS sinks, and harden WhatsApp OTP intended redirects.',
                'Improve Google/Facebook login settings UX (credentials first, collapsed setup guide, compact callback copy) and polish customer/admin login performance and Email/OTP switching.',
            ],
        ],
        'ms' => [
            'summary' => 'Storefront lebih pantas, fon self-hosted, CSP lebih ketat, dan tetapan social login lebih selamat.',
            'changes' => [
                'Percepat halaman storefront dengan lazy-load input telefon, datepicker, SweetAlert2, zoom produk, dan lightbox hanya bila diperlukan, serta buang jQuery tidak digunakan daripada bundle global.',
                'Hos sendiri fon paparan dengan @fontsource (latin 300/400/500) dan buang permintaan Google Fonts daripada layout storefront dan auth.',
                'Kuatkuasakan Content-Security-Policy secara lalai, sanitaskan aset header/footer tersuai kepada allowlist skrip HTTPS, escape sink XSS berkaitan social login, dan perkukuh redirect intended WhatsApp OTP.',
                'Perbaiki UX tetapan log masuk Google/Facebook (kredensial dahulu, panduan setup tertutup, salin callback padat) serta poles prestasi log masuk pelanggan/admin dan suis Email/OTP.',
            ],
        ],
    ],
    '4.11.8' => [
        'date' => '2026-08-22 01:18',
        'en' => [
            'summary' => 'Safer appointment availability, reliable checkout slots, and refreshed booking UI dependencies.',
            'changes' => [
                'Require a complete branch and treatment context before returning treatment appointment slots.',
                'Reject inactive branches, unavailable treatments, and beauticians who are not assigned to the selected branch.',
                'Prevent checkout from sending incomplete slot requests and keep date exceptions aligned with the selected treatment rules.',
                'Refresh the searchable admin selectors and storefront slider dependencies, with rebuilt production assets and expanded availability tests.',
            ],
        ],
        'ms' => [
            'summary' => 'Ketersediaan temujanji lebih selamat, slot checkout lebih tepat, dan kebergantungan UI tempahan dikemas kini.',
            'changes' => [
                'Wajibkan konteks cawangan dan rawatan yang lengkap sebelum slot temujanji rawatan dipulangkan.',
                'Tolak cawangan tidak aktif, rawatan tidak tersedia, dan beautician yang tidak ditugaskan kepada cawangan yang dipilih.',
                'Halang checkout daripada menghantar permintaan slot tidak lengkap dan selaraskan pengecualian tarikh dengan peraturan rawatan yang dipilih.',
                'Kemas kini selector carian admin dan kebergantungan slider storefront, bina semula aset production, serta tambah liputan ujian ketersediaan.',
            ],
        ],
    ],
    '4.11.7' => [
        'date' => '2026-08-21 23:17',
        'en' => [
            'summary' => 'Appointment availability admin, SweetNotification alerts, sidebar refresh, and checkout date-picker fix.',
            'changes' => [
                'Add Appointment Availability management (branch weekly hours, treatment overrides, and date overrides) with checkout date enable rules.',
                'Replace toaster flashes with SweetNotification (SweetAlert2) centered modals across admin and storefront.',
                'Reorganize the admin sidebar into clearer groups and nest appointment tools under Appointments.',
                'Fix checkout Flatpickr crash when no availability dates are loaded (enable was undefined).',
            ],
        ],
        'ms' => [
            'summary' => 'Ketersediaan temujanji admin, notifikasi SweetNotification, refresh sidebar, dan pembetulan date picker checkout.',
            'changes' => [
                'Tambah pengurusan Ketersediaan Temujanji (jadual mingguan cawangan, override rawatan, dan override tarikh) dengan peraturan tarikh di checkout.',
                'Ganti flash toaster dengan SweetNotification (SweetAlert2) modal berpusat di admin dan storefront.',
                'Susun semula sidebar admin kepada kumpulan lebih jelas dan letak alat temujanji di bawah Appointments.',
                'Baiki ralat Flatpickr di checkout bila tiada tarikh ketersediaan (enable tidak boleh undefined).',
            ],
        ],
    ],
    '4.11.6' => [
        'date' => '2026-08-21 12:32',
        'en' => [
            'summary' => 'Beautician self-registration profile photo, portal redirect, and mobile register UX fixes.',
            'changes' => [
                'Allow optional profile photo upload on beautician registration (JPG/PNG/WebP, compressed to WebP).',
                'Log newly registered beauticians into the portal dashboard; pending profiles stay hidden from customer booking but can use the portal.',
                'Add a fourth registration benefit for creating customer bookings, plus bottom spacing and mobile topbar fixes (sign-in tap target, language dropdown alignment).',
            ],
        ],
        'ms' => [
            'summary' => 'Foto profil daftar beautician, redirect portal, dan pembetulan UX daftar di mobile.',
            'changes' => [
                'Benarkan muat naik foto profil pilihan semasa daftar beautician (JPG/PNG/WebP, dimampatkan ke WebP).',
                'Log masuk beautician baharu ke dashboard portal; profil pending kekal tersembunyi daripada tempahan pelanggan tetapi boleh guna portal.',
                'Tambah manfaat keempat untuk buat tempahan pelanggan, plus jarak bawah dan pembetulan topbar mobile (ketikan log masuk, penjajaran dropdown bahasa).',
            ],
        ],
    ],
    '4.11.5' => [
        'date' => '2026-08-21 12:05',
        'en' => [
            'summary' => 'TBA scheduling defaults, portal privacy drawer, and beautician register split-scroll layout.',
            'changes' => [
                'Add Schedule later (TBA) booking flow with admin/portal scheduling onto available calendar slots.',
                'Default appointment mode to Schedule later (TBA) on checkout, beautician portal, and reservation manual booking.',
                'Allow beauticians to open other specialists’ appointment drawers while blurring customer phone and email.',
                'Fix beautician registration desktop/tablet layout: fixed left story panel with an independently scrolling form.',
            ],
        ],
        'ms' => [
            'summary' => 'Lalai jadual TBA, drawer privasi portal, dan layout scroll berasingan halaman daftar beautician.',
            'changes' => [
                'Tambah aliran tempahan Jadual kemudian (TBA) dengan penjadualan admin/portal ke slot kalendar tersedia.',
                'Jadikan Jadual kemudian (TBA) sebagai pilihan lalai di checkout, portal beautician, dan tempahan manual reservation.',
                'Benarkan beautician buka drawer temujanji spesialis lain dengan telefon dan e-mel pelanggan diburamkan.',
                'Baiki layout daftar beautician desktop/tablet: panel kiri kekal, borang kanan sahaja yang scroll.',
            ],
        ],
    ],
    '4.11.4' => [
        'date' => '2026-08-21 09:25',
        'en' => [
            'summary' => 'Uniform Email/OTP segmented switch on admin login across all devices.',
            'changes' => [
                'Use the same pill segmented Email login / Login with OTP switch on desktop, tablet, and mobile.',
            ],
        ],
        'ms' => [
            'summary' => 'Suis Email/OTP seragam pada login admin merentas semua peranti.',
            'changes' => [
                'Guna suis bersegmen Email login / Login with OTP yang sama pada desktop, tablet, dan mobile.',
            ],
        ],
    ],
    '4.11.3' => [
        'date' => '2026-08-21 09:20',
        'en' => [
            'summary' => 'Beautician registration mobile UX polish and square logo setting.',
            'changes' => [
                'Fix mobile scroll trapping on the beautician registration story panel.',
                'Add Storefront Square Logo (1:1) setting and use it on the registration brand mark.',
                'Clarify form field borders and unify smaller mobile typography on the registration page.',
            ],
        ],
        'ms' => [
            'summary' => 'Kemas UX daftar beautician mobile dan tetapan logo segi empat.',
            'changes' => [
                'Baiki scroll mobile yang tersekat pada panel cerita halaman daftar beautician.',
                'Tambah tetapan Logo Segi Empat (1:1) di Storefront dan guna pada tanda jenama daftar.',
                'Jelas border medan borang dan seragamkan tipografi mobile yang lebih kecil pada halaman daftar.',
            ],
        ],
    ],
    '4.11.2' => [
        'date' => '2026-08-20 21:00',
        'en' => [
            'summary' => 'Sticky admin footer stays visible on scroll.',
            'changes' => [
                'Change admin footer from absolute to fixed positioning so it remains pinned at the bottom of the viewport while scrolling.',
                'Add z-index: 1040 to keep footer above content but below modals/sidebar.',
            ],
        ],
        'ms' => [
            'summary' => 'Footer admin melekat nampak semasa scroll.',
            'changes' => [
                'Tukar footer admin dari absolute ke fixed positioning agar kekal terpang di bawah viewport semasa scroll.',
                'Tambah z-index: 1040 supaya footer di atas kandungan tapi di bawah modal/sidebar.',
            ],
        ],
    ],
    '4.11.1' => [
        'date' => '2026-08-20 18:47',
        'en' => [
            'summary' => 'Mobile bottom nav flush bar and admin login form UX polish.',
            'changes' => [
                'Convert storefront mobile bottom navigation from floating pill to full-width flush bar.',
                'Modernize admin login form: segmented Email/OTP switch, white page background, and distinct beautician register button.',
                'Update beautician register CTA copy to "New beautician? Register Now".',
            ],
        ],
        'ms' => [
            'summary' => 'Bar navigasi bawah mobile rata dan kemas UX borang login admin.',
            'changes' => [
                'Tukar navigasi bawah storefront mobile dari pill terapung kepada bar penuh lebar.',
                'Modenkan borang login admin: suis Email/OTP, latar putih, dan butang daftar beautician berasingan.',
                'Kemas teks CTA daftar beautician kepada "Beautician baharu? Daftar sekarang".',
            ],
        ],
    ],
    '4.11.0' => [
        'date' => '2026-08-20 10:32',
        'en' => [
            'summary' => 'Homepage tablet and mobile UX: tab sliders, footer layout, blog cards, and unified slider controls.',
            'changes' => [
                'Fix homepage product tab switching with index-based state and reliable Swiper remounts.',
                'Add tablet homepage layout for featured categories, blog slider, and hidden header spacing.',
                'Redesign product slider prev/next controls with pill navigation, dots, and counter mode for large sets.',
                'Add tablet footer with four-column link layout and full-width tags row; keep accordion footer on mobile only.',
                'Modernize bottom navigation, blog cards (1:1 images, excerpt), and section View All badges.',
            ],
        ],
        'ms' => [
            'summary' => 'UX tablet dan mobile homepage: tab slider, susun atur footer, kad blog, dan kawalan slider seragam.',
            'changes' => [
                'Baiki pertukaran tab produk homepage dengan state berasaskan indeks dan remount Swiper yang stabil.',
                'Tambah susun atur tablet untuk kategori pilihan, slider blog, dan jarak header.',
                'Reka semula kawalan prev/next slider produk dengan navigasi pill, dots, dan mod counter.',
                'Tambah footer tablet empat kolum dengan baris tags penuh; kekalkan footer accordion pada mobile sahaja.',
                'Modenkan bottom navigation, kad blog (imej 1:1, petikan), dan badge View All.',
            ],
        ],
    ],
    '4.10.0' => [
        'date' => '2026-08-20 08:39',
        'en' => [
            'summary' => 'Fix PWA admin settings and add a storefront install modal with platform-specific instructions.',
            'changes' => [
                'Persist PWA enablement and related fields from the Settings PWA tab.',
                'Use a valid iOS status-bar style instead of a colour picker, and generate icons from the media file path.',
                'Show a storefront install modal with English and Bahasa Malaysia copy when PWA is enabled.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki tetapan PWA admin dan tambah modal pemasangan storefront dengan arahan mengikut platform.',
            'changes' => [
                'Simpan status PWA dan medan berkaitan dari tab Tetapan PWA.',
                'Gunakan gaya bar status iOS yang sah, dan jana ikon dari path fail media.',
                'Paparkan modal pemasangan storefront dengan teks English dan Bahasa Malaysia apabila PWA diaktifkan.',
            ],
        ],
    ],
    '4.9.99' => [
        'date' => '2026-08-20 00:30',
        'en' => [
            'summary' => 'Add backend-managed Malaysian public holidays with admin import/edit tools and calendar-wide holiday display.',
            'changes' => [
                'Add treatment_public_holidays table, import service, and holiday range API for calendars.',
                'Add admin Holidays page with import-by-year, manual add, edit, delete, search, sort, and pagination tools.',
                'Show holiday names, states, and type-aware badges across admin and portal calendars.',
                'Fix holiday date-key mapping so saved holidays appear consistently in all calendar views.',
                'Seed 2026 holiday master data through a committed data migration for production rollout.',
            ],
        ],
        'ms' => [
            'summary' => 'Tambah cuti umum Malaysia berasaskan backend dengan alat import/edit admin dan paparan cuti di semua kalendar.',
            'changes' => [
                'Tambah jadual treatment_public_holidays, servis import, dan API julat cuti untuk kalendar.',
                'Tambah halaman Holidays admin dengan import mengikut tahun, tambah manual, edit, buang, carian, susun, dan pagination.',
                'Paparkan nama cuti, negeri, dan badge mengikut jenis di seluruh kalendar admin dan portal.',
                'Betulkan pemetaan date-key cuti supaya cuti yang disimpan muncul konsisten dalam semua paparan kalendar.',
                'Seed data master holiday 2026 melalui data migration yang dikomit untuk rollout production.',
            ],
        ],
    ],
    '4.9.98' => [
        'date' => '2026-08-19 20:05',
        'en' => [
            'summary' => 'Dashboard welcome banner redesign, Today Orders stat, per-branch sales analytics, and orders table improvements.',
            'changes' => [
                'Redesign welcome banner with pink sakura theme and 3D trophy icon.',
                'Replace Pending Payment stat card with Today Orders (click filters orders by today).',
                'Add per-branch stacked bar chart in sales analytics with distinct colors.',
                'Add Beautician column to orders table; remove Customer Email column.',
                'Status column now shows treatment status instead of order status.',
                'Truncate long customer/beautician names in tables.',
                'Full MYR currency format on dashboard stat cards.',
                'Glassmorphism styling for report stat cards.',
                'Solid badge colors across admin and storefront.',
                'Add date filter support in orders index.',
            ],
        ],
        'ms' => [
            'summary' => 'Reka bentuk semula banner selamat datang, stat Pesanan Hari Ini, analitik jualan per cawangan, dan penambahbaikan jadual pesanan.',
            'changes' => [
                'Reka bentuk semula banner selamat datang dengan tema pink sakura dan ikon trofi 3D.',
                'Tukar kad stat Bayaran Tertunda kepada Pesanan Hari Ini (klik tapis pesanan hari ini).',
                'Tambah carta bar bertindan per cawangan dalam analitik jualan dengan warna berbeza.',
                'Tambah kolom Beautician dalam jadual pesanan; buang kolom Emel Pelanggan.',
                'Kolom status kini menunjukkan status rawatan bukan status pesanan.',
                'Potong nama pelanggan/beautician yang panjang dalam jadual.',
                'Format mata wang MYR penuh pada kad stat dashboard.',
                'Gaya glassmorphism untuk kad stat laporan.',
                'Warna badge pepejal di seluruh admin dan storefront.',
                'Tambah sokongan tapis tarikh di halaman pesanan.',
            ],
        ],
    ],
    '4.9.95' => [
        'date' => '2026-08-19 18:15',
        'en' => [
            'summary' => 'Redesign admin dashboard with Quick Actions, Top Beauticians, and modern stat cards.',
            'changes' => [
                'Add Quick Actions card grid (Add Product, Approve Users, View Reports, Settings).',
                'Add Top Beauticians panel with profile images, job title, branch, orders, and revenue (last 3 months).',
                'Redesign stat cards with colored top accent bars and clean white cards.',
                'Add welcome greeting header with date display.',
                'Apply Plus Jakarta Sans font for headings.',
                'Release notes now show date and time for each version.',
            ],
        ],
        'ms' => [
            'summary' => 'Reka bentuk semula dashboard admin dengan Tindakan Pantas, Beautician Terbaik, dan kad statistik moden.',
            'changes' => [
                'Tambah grid kad Tindakan Pantas (Tambah Produk, Luluskan Pengguna, Lihat Laporan, Tetapan).',
                'Tambah panel Beautician Terbaik dengan gambar profil, jawatan, cawangan, pesanan, dan hasil (3 bulan lepas).',
                'Reka bentuk semula kad statistik dengan bar aksen berwarna di atas dan kad putih bersih.',
                'Tambah pengepala ucapan selamat datang dengan paparan tarikh.',
                'Guna font Plus Jakarta Sans untuk tajuk.',
                'Nota pelepasan kini memaparkan tarikh dan masa untuk setiap versi.',
            ],
        ],
    ],
    '4.9.92' => [
        'date' => '2026-08-18 14:00',
        'en' => [
            'summary' => 'Stop cached configuration from silently downgrading the cache driver and log level.',
            'changes' => [
                'Resolve the cache driver from configuration instead of env() at runtime, so warming production caches no longer drops a Redis install back to the file store.',
                'Read mail host, port, credentials and sender details from configuration, keeping the log-mailer fallback when no SMTP host is set.',
                'Leave the log level to config/logging.php so cached configuration no longer forces both channels back to error.',
                'Align the mail configuration defaults with the values the application actually expects.',
            ],
        ],
        'ms' => [
            'summary' => 'Halang konfigurasi yang di-cache daripada menurunkan pemacu cache dan aras log secara senyap.',
            'changes' => [
                'Selesaikan pemacu cache daripada konfigurasi dan bukan env() ketika runtime, supaya memanaskan cache production tidak lagi menjatuhkan pemasangan Redis kembali ke storan fail.',
                'Baca hos mel, port, kelayakan dan butiran penghantar daripada konfigurasi, sambil mengekalkan sandaran log-mailer apabila tiada hos SMTP ditetapkan.',
                'Serahkan aras log kepada config/logging.php supaya konfigurasi yang di-cache tidak lagi memaksa kedua-dua saluran kembali ke error.',
                'Selaraskan nilai lalai konfigurasi mel dengan nilai yang sebenarnya dijangka oleh aplikasi.',
            ],
        ],
    ],
    '4.9.91' => [
        'en' => [
            'summary' => 'Key the storefront response cache by currency and expire it when home page content changes.',
            'changes' => [
                'Include the visitor currency in the response cache key, so prices rendered in one currency are no longer served to visitors browsing in another.',
                'Expire cached storefront HTML when a slider, category, brand, product, or flash sale is saved or deleted.',
                'Keep edited home page content visible immediately instead of waiting for the cache lifetime to run out.',
            ],
        ],
        'ms' => [
            'summary' => 'Kunci response cache storefront mengikut mata wang dan luputkan ia apabila kandungan halaman utama berubah.',
            'changes' => [
                'Masukkan mata wang pelawat ke dalam kunci response cache, supaya harga dalam satu mata wang tidak lagi dihidangkan kepada pelawat yang melayari dalam mata wang lain.',
                'Luputkan HTML storefront yang di-cache apabila slider, kategori, jenama, produk atau flash sale disimpan atau dipadam.',
                'Kekalkan kandungan halaman utama yang disunting supaya kelihatan serta-merta tanpa menunggu tempoh cache tamat.',
            ],
        ],
    ],
    '4.9.90' => [
        'en' => [
            'summary' => 'Make the storefront response cache safe to switch on by keeping CSRF tokens per visitor.',
            'changes' => [
                'Stop the full-page response cache from serving one visitor CSRF token to every other guest, which rejected all guest form and cart submissions with a 419 error.',
                'Store cached HTML with a token placeholder and substitute the current session token when the page is served from cache.',
                'Storefront home and CMS pages now respond from cache without breaking add-to-cart, wishlist, newsletter, or login requests.',
            ],
        ],
        'ms' => [
            'summary' => 'Jadikan response cache storefront selamat diaktifkan dengan mengekalkan token CSRF setiap pelawat.',
            'changes' => [
                'Halang response cache halaman penuh daripada memberikan token CSRF seorang pelawat kepada semua tetamu lain, yang menolak setiap penghantaran borang dan troli tetamu dengan ralat 419.',
                'Simpan HTML cache dengan placeholder token dan gantikan dengan token sesi semasa ketika halaman dihidangkan daripada cache.',
                'Halaman utama storefront dan halaman CMS kini dihidangkan daripada cache tanpa merosakkan permintaan tambah-ke-troli, wishlist, newsletter atau log masuk.',
            ],
        ],
    ],
    '4.9.89' => [
        'en' => [
            'summary' => 'Cut admin panel database load by memoizing repeated role checks and indexing order lookups.',
            'changes' => [
                'Memoize role-name and role-id checks per request, removing 13-15 duplicate permission queries from every admin page render.',
                'Add an appointment-date index so dashboard appointment widgets stop scanning the whole orders table.',
                'Apply the pending order performance and customer lookup indexes to the orders table.',
                'Compare appointment dates directly instead of wrapping the column in a date function, so the new index is actually used.',
                'Load module route files with require instead of require_once, so a second application boot in the same process registers all routes.',
            ],
        ],
        'ms' => [
            'summary' => 'Kurangkan beban pangkalan data panel admin dengan memoize semakan peranan berulang dan index carian pesanan.',
            'changes' => [
                'Memoize semakan nama dan id peranan bagi setiap request, membuang 13-15 query kebenaran berulang daripada setiap paparan halaman admin.',
                'Tambah index tarikh temujanji supaya widget temujanji dashboard tidak lagi mengimbas keseluruhan jadual pesanan.',
                'Laksanakan index prestasi pesanan dan carian pelanggan yang masih tertunggak pada jadual pesanan.',
                'Bandingkan tarikh temujanji secara terus tanpa membalut lajur dengan fungsi tarikh, supaya index baharu benar-benar digunakan.',
                'Muatkan fail route modul dengan require dan bukan require_once, supaya boot aplikasi kedua dalam proses yang sama mendaftarkan semua route.',
            ],
        ],
    ],
    '4.9.88' => [
        'en' => [
            'summary' => 'Add secure public beautician onboarding and a richer Kosmetik storefront experience.',
            'changes' => [
                'Add a protected public beautician registration flow with validated roles, active branch selection, spam throttling, and duplicate contact checks.',
                'Keep newly submitted beautician profiles hidden and restricted to an approval-pending screen until an administrator activates them.',
                'Introduce a fully responsive branded registration experience with structured profile, placement, and portal-security sections.',
                'Fix root-category browsing so Kosmetik includes products assigned to active child categories and uses real category URLs.',
                'Add a bilingual Kosmetik showcase, useful subcategory availability states, category-specific page titles, and refreshed production assets.',
            ],
        ],
        'ms' => [
            'summary' => 'Tambah onboarding beautician awam yang selamat dan pengalaman storefront Kosmetik yang lebih lengkap.',
            'changes' => [
                'Tambah aliran pendaftaran beautician awam dengan pengesahan peranan, pemilihan cawangan aktif, perlindungan spam dan semakan hubungan pendua.',
                'Kekalkan profil beautician baharu sebagai tersembunyi dan hadkan akses kepada skrin menunggu kelulusan sehingga pentadbir mengaktifkannya.',
                'Perkenalkan pengalaman pendaftaran berjenama yang responsif dengan bahagian profil, penempatan dan keselamatan portal yang tersusun.',
                'Betulkan pelayaran kategori induk supaya Kosmetik merangkumi produk dalam kategori anak aktif dan menggunakan URL kategori sebenar.',
                'Tambah paparan Kosmetik dwibahasa, status ketersediaan subkategori, tajuk halaman khusus kategori dan aset production yang dikemas kini.',
            ],
        ],
    ],
    '4.9.87' => [
        'en' => [
            'summary' => 'Refine the Operations Health interface into a calmer and more structured production dashboard.',
            'changes' => [
                'Replace the legacy high-contrast status boxes with balanced semantic metric cards and a concise overall-health banner.',
                'Introduce consistent panel hierarchy, spacing, icons, tags, action buttons, and informative empty states.',
                'Consolidate retention, legal-hold, observability, and audit information into clearer responsive dashboard sections.',
                'Improve table readability and horizontal overflow behavior across desktop, tablet, and mobile viewports.',
                'Preserve the existing authorization, privacy boundaries, queue controls, and operational behavior.',
            ],
        ],
        'ms' => [
            'summary' => 'Perkemas antaramuka Kesihatan Operasi menjadi dashboard production yang lebih tenang dan tersusun.',
            'changes' => [
                'Gantikan kotak status lama yang terlalu terang dengan kad metrik semantik seimbang dan banner kesihatan keseluruhan yang ringkas.',
                'Tambah hierarchy panel, spacing, ikon, tag, butang tindakan dan empty state yang konsisten.',
                'Susun retention, legal hold, observability dan maklumat audit kepada bahagian dashboard responsif yang lebih jelas.',
                'Tingkatkan kebolehbacaan jadual dan kawalan horizontal overflow untuk paparan desktop, tablet serta mobile.',
                'Kekalkan authorization, sempadan privasi, kawalan queue dan tingkah laku operasi sedia ada.',
            ],
        ],
    ],
    '4.9.86' => [
        'en' => [
            'summary' => 'Add a permission-scoped operational control center for production health and guarded administration.',
            'changes' => [
                'Add an operations dashboard for queue, scheduler, retention, CSP, and slow-query health without exposing sensitive job payloads or exceptions.',
                'Add separately authorized and rate-limited pending-job cancellation, failed-job retry, and consultation legal-hold controls.',
                'Add immutable operational audit records with privacy-safe action metadata, actor context, IP address, and user agent.',
                'Add scheduler heartbeat monitoring, sidebar health alerts, and bilingual administration labels.',
                'Add database migration, authorization contracts, view compilation coverage, and safe job-class extraction tests.',
            ],
        ],
        'ms' => [
            'summary' => 'Tambah pusat kawalan operasi berasaskan permission untuk kesihatan production dan pentadbiran terkawal.',
            'changes' => [
                'Tambah dashboard operasi untuk kesihatan queue, scheduler, retention, CSP dan slow query tanpa mendedahkan payload job atau exception sensitif.',
                'Tambah kawalan berasingan authorization dan rate limit untuk membatalkan pending job, retry failed job serta legal hold consultation.',
                'Tambah rekod audit operasi immutable dengan metadata tindakan yang menjaga privasi, konteks pelaku, alamat IP dan user agent.',
                'Tambah pemantauan heartbeat scheduler, amaran kesihatan pada sidebar dan label pentadbiran dwibahasa.',
                'Tambah migration database, kontrak authorization, coverage kompilasi view dan ujian extraction class job yang selamat.',
            ],
        ],
    ],
    '4.9.85' => [
        'en' => [
            'summary' => 'Add production privacy lifecycle, queue reliability, CSP reporting, and database observability controls.',
            'changes' => [
                'Add dry-run-first consultation retention with legal holds, audited executions, bounded deletion, and private document cleanup.',
                'Add queue health thresholds and move verified CHIP webhook processing into a unique retryable background job.',
                'Add a rate-limited CSP report endpoint that strips URL query strings and hashes client IP addresses before logging.',
                'Add privacy-safe slow-query logging, expanded read-only production benchmarks, and customer order lookup indexes.',
                'Add authorization and operational regression contracts, production configuration guidance, and rollback procedures.',
            ],
        ],
        'ms' => [
            'summary' => 'Tambah kawalan lifecycle privasi, reliability queue, pelaporan CSP dan pemerhatian database untuk production.',
            'changes' => [
                'Tambah retention consultation berasaskan dry-run dengan legal hold, execution audit, pemadaman terhad dan cleanup dokumen private.',
                'Tambah threshold kesihatan queue dan pindahkan pemprosesan webhook CHIP yang sah kepada background job unik dengan retry.',
                'Tambah endpoint laporan CSP ber-rate-limit yang membuang query string URL dan hash alamat IP sebelum logging.',
                'Tambah slow-query logging yang menjaga privasi, benchmark production read-only yang diperluas dan index carian order pelanggan.',
                'Tambah kontrak regresi authorization dan operasi, panduan konfigurasi production serta prosedur rollback.',
            ],
        ],
    ],
    '4.9.84' => [
        'en' => [
            'summary' => 'Restore storefront rendering after the secure-link release.',
            'changes' => [
                'Replace an invalid inline Blade assignment in the mobile promotion section with a standard compiled PHP block.',
                'Restore HTTP 200 rendering for the localized storefront homepage while retaining CTA URL normalization.',
                'Validate the hotfix with a real homepage request in addition to the automated regression suite.',
            ],
        ],
        'ms' => [
            'summary' => 'Pulihkan rendering storefront selepas release pautan selamat.',
            'changes' => [
                'Gantikan assignment Blade inline yang tidak sah pada seksyen promosi mudah alih dengan blok PHP standard yang boleh dikompilasi.',
                'Pulihkan respons HTTP 200 untuk homepage storefront berlokalisasi sambil mengekalkan normalisasi URL CTA.',
                'Sahkan hotfix menggunakan request homepage sebenar selain suite ujian regresi automatik.',
            ],
        ],
    ],
    '4.9.83' => [
        'en' => [
            'summary' => 'Protect stored integration credentials and repair legacy storefront links at render time.',
            'changes' => [
                'Encrypt sensitive settings at rest with backward-compatible reads and a production-safe data migration.',
                'Keep decrypted credentials out of persistent settings caches and clear legacy cache entries during deployment.',
                'Stop prefilling passwords and service-account JSON in admin HTML while preserving existing secrets when a field is left blank.',
                'Rehome legacy localhost storefront links to the current production origin and install path without rewriting genuine external destinations.',
                'Reject unsafe CTA schemes and add regression coverage for encryption, legacy plaintext compatibility, redaction, and URL normalization.',
            ],
        ],
        'ms' => [
            'summary' => 'Lindungi kredensial integrasi yang disimpan dan baiki pautan storefront lama semasa rendering.',
            'changes' => [
                'Enkripsi tetapan sensitif at rest dengan bacaan backward-compatible dan migration data yang selamat untuk production.',
                'Keluarkan kredensial yang dinyah-enkripsi daripada persistent settings cache serta bersihkan cache lama semasa deployment.',
                'Hentikan prefill password dan JSON service account dalam HTML admin sambil mengekalkan secret sedia ada apabila medan dibiarkan kosong.',
                'Alihkan pautan storefront localhost lama kepada origin dan install path production semasa tanpa mengubah destinasi luaran sebenar.',
                'Tolak skim CTA yang tidak selamat dan tambah ujian regresi untuk encryption, keserasian plaintext lama, redaction, dan normalisasi URL.',
            ],
        ],
    ],
    '4.9.82' => [
        'en' => [
            'summary' => 'Make in-place GitHub updates safely reconcile files retired by newer releases.',
            'changes' => [
                'Add a cumulative release deletion manifest for obsolete PayPal, MercadoPago, and retired payment gateway files.',
                'Move stale files into timestamped private quarantine instead of deleting them permanently, preserving a rollback path.',
                'Protect environment, storage, uploads, vendor, Git metadata, and active release paths from manifest cleanup.',
                'Run cleanup during GitHub deployment, normal post-update tasks, and a one-time bootstrap script for servers upgrading from the legacy overlay updater.',
                'Add regression coverage for safe paths, active-file protection, directory quarantine, and idempotent repeated updates.',
            ],
        ],
        'ms' => [
            'summary' => 'Pastikan kemas kini GitHub secara in-place menyelaraskan fail yang ditamatkan oleh release baharu dengan selamat.',
            'changes' => [
                'Tambah manifest pemadaman release kumulatif untuk fail PayPal, MercadoPago, dan gateway pembayaran lama.',
                'Pindahkan fail lapuk ke kuarantin private bertimestamp tanpa memadamkannya secara kekal supaya rollback masih tersedia.',
                'Lindungi environment, storage, muat naik, vendor, metadata Git, dan path release aktif daripada cleanup manifest.',
                'Jalankan cleanup semasa deployment GitHub, tugas post-update biasa, dan script bootstrap sekali jalan bagi pelayan yang menaik taraf daripada updater overlay lama.',
                'Tambah liputan regresi bagi path selamat, perlindungan fail aktif, kuarantin direktori, dan kemas kini berulang yang idempotent.',
            ],
        ],
    ],
    '4.9.81' => [
        'en' => [
            'summary' => 'Retire every unused payment gateway so checkout supports only CHIP, COD, and bank transfer.',
            'changes' => [
                'Remove thirteen unused gateway implementations, callbacks, settings screens, translations, and browser checkout flows.',
                'Remove eight retired payment SDK packages and their transitive dependencies from the Composer lock file.',
                'Add a production-safe migration that removes retired gateway settings and credentials without changing historical orders.',
                'Rebuild production assets without retired payment scripts while preserving CHIP, COD, and bank transfer checkout behavior.',
                'Add regression coverage and confirm the production dependency graph has no known security advisories.',
            ],
        ],
        'ms' => [
            'summary' => 'Tamatkan semua gateway pembayaran yang tidak digunakan supaya checkout hanya menyokong CHIP, COD, dan pindahan bank.',
            'changes' => [
                'Buang tiga belas implementasi gateway, callback, skrin tetapan, terjemahan, dan aliran checkout browser yang tidak digunakan.',
                'Buang lapan pakej SDK pembayaran lama bersama dependency transitifnya daripada fail lock Composer.',
                'Tambah migration selamat untuk production yang membuang tetapan dan kredensial gateway lama tanpa mengubah sejarah order.',
                'Bina semula aset production tanpa skrip pembayaran lama sambil mengekalkan aliran checkout CHIP, COD, dan pindahan bank.',
                'Tambah liputan regresi dan sahkan graph dependency production tidak mempunyai advisory keselamatan yang diketahui.',
            ],
        ],
    ],
    '4.9.80' => [
        'en' => [
            'summary' => 'Retire the unused MercadoPago checkout integration and remove its abandoned SDK dependency graph.',
            'changes' => [
                'Remove the MercadoPago gateway, response verification, browser SDK, settings credentials, translations, and checkout flow.',
                'Remove mercadopago/dx-php and its abandoned Doctrine Annotations dependency graph from the Composer lock file.',
                'Rebuild the admin and storefront production assets without MercadoPago browser code.',
                'Add regression coverage that prevents the MercadoPago SDK and Doctrine Annotations from being reintroduced silently.',
                'Confirm the production dependency graph has no known security advisories or abandoned packages.',
            ],
        ],
        'ms' => [
            'summary' => 'Tamatkan integrasi checkout MercadoPago yang tidak digunakan dan buang graph dependency SDK yang abandoned.',
            'changes' => [
                'Buang gateway MercadoPago, pengesahan respons, browser SDK, kredensial tetapan, terjemahan, dan aliran checkout.',
                'Buang mercadopago/dx-php bersama graph dependency Doctrine Annotations yang abandoned daripada fail lock Composer.',
                'Bina semula aset production admin dan storefront tanpa kod browser MercadoPago.',
                'Tambah liputan regresi yang menghalang SDK MercadoPago dan Doctrine Annotations daripada dimasukkan semula secara senyap.',
                'Sahkan graph dependency production tidak mempunyai advisory keselamatan atau pakej abandoned yang diketahui.',
            ],
        ],
    ],
    '4.9.79' => [
        'en' => [
            'summary' => 'Upgrade Socialite and PHP-JWT to close the remaining Composer security advisory without breaking social login.',
            'changes' => [
                'Upgrade Laravel Socialite to 5.29.0 and firebase/php-jwt to 7.1.0 through their supported compatibility path.',
                'Pin the User module to the secure JWT 7 dependency line so future dependency resolution cannot silently restore JWT 6.',
                'Add an RS256 Google ID-token regression test covering Socialite issuer, audience, signature, and claim verification.',
                'Confirm the production dependency graph has no known security advisories after the upgrade.',
                'Document the required manual Composer install because GitHub deployments intentionally preserve the server vendor directory.',
            ],
        ],
        'ms' => [
            'summary' => 'Naik taraf Socialite dan PHP-JWT untuk menutup baki advisory keselamatan Composer tanpa merosakkan login sosial.',
            'changes' => [
                'Naik taraf Laravel Socialite kepada 5.29.0 dan firebase/php-jwt kepada 7.1.0 melalui laluan keserasian yang disokong.',
                'Kunci modul User kepada dependency JWT 7 yang selamat supaya dependency resolution akan datang tidak mengembalikan JWT 6 secara senyap.',
                'Tambah ujian regresi Google ID token RS256 yang meliputi pengesahan issuer, audience, signature, dan claim melalui Socialite.',
                'Sahkan graph dependency production tidak lagi mempunyai advisory keselamatan yang diketahui selepas naik taraf.',
                'Dokumentasikan keperluan Composer install secara manual kerana deployment GitHub sengaja mengekalkan direktori vendor server.',
            ],
        ],
    ],
    '4.9.78' => [
        'en' => [
            'summary' => 'Show the complete Settings navigation as one full-height menu without nested scrolling.',
            'changes' => [
                'Remove the viewport-height and sticky constraints that truncated the Settings navigation card.',
                'Let the sidebar grow naturally to contain every expanded settings group and menu item.',
                'Remove the sidebar internal scrollbar so navigation follows the main page scroll only.',
                'Rebuild the production Settings JavaScript, stylesheet, and asset manifest.',
            ],
        ],
        'ms' => [
            'summary' => 'Paparkan keseluruhan navigasi Settings sebagai satu menu penuh tanpa nested scrolling.',
            'changes' => [
                'Buang kekangan tinggi viewport dan sticky yang memotong kad navigasi Settings.',
                'Benarkan sidebar mengembang secara semula jadi untuk memuatkan setiap kumpulan dan item menu Settings.',
                'Buang scrollbar dalaman sidebar supaya navigasi hanya mengikuti scroll halaman utama.',
                'Bina semula JavaScript, stylesheet, dan manifest aset Settings untuk production.',
            ],
        ],
    ],
    '4.9.77' => [
        'en' => [
            'summary' => 'Make the Settings navigation sidebar fit the available viewport height at every scroll position.',
            'changes' => [
                'Calculate the sidebar height from its actual viewport position so its lower edge remains fully visible.',
                'Expand the sticky sidebar as the page scrolls while retaining vertical navigation scrolling.',
                'Resynchronize the available height after viewport resizing or layout changes such as dismissed alerts.',
                'Rebuild the production Settings JavaScript, stylesheet, and asset manifest.',
            ],
        ],
        'ms' => [
            'summary' => 'Pastikan sidebar navigasi Settings memenuhi tinggi viewport yang tersedia pada setiap kedudukan scroll.',
            'changes' => [
                'Kira tinggi sidebar daripada kedudukan viewport sebenar supaya bahagian bawahnya sentiasa kelihatan penuh.',
                'Besarkan sidebar sticky apabila halaman discroll sambil mengekalkan vertical scrolling navigasi.',
                'Selaraskan semula tinggi selepas viewport diresize atau layout berubah seperti alert yang ditutup.',
                'Bina semula JavaScript, stylesheet, dan manifest aset Settings untuk production.',
            ],
        ],
    ],
    '4.9.76' => [
        'en' => [
            'summary' => 'Remove unintended horizontal scrolling from the Settings navigation sidebar.',
            'changes' => [
                'Constrain the desktop Settings navigation to vertical scrolling without exposing a horizontal scrollbar.',
                'Preserve the existing responsive horizontal tab navigation on smaller screens.',
                'Rebuild the production Settings stylesheet and asset manifest with the corrected overflow behavior.',
            ],
        ],
        'ms' => [
            'summary' => 'Buang horizontal scrolling yang tidak disengajakan daripada sidebar navigasi Settings.',
            'changes' => [
                'Hadkan navigasi Settings desktop kepada vertical scrolling tanpa memaparkan horizontal scrollbar.',
                'Kekalkan navigasi tab mendatar yang responsif pada skrin lebih kecil.',
                'Bina semula stylesheet Settings production dan manifest aset dengan tingkah laku overflow yang telah dibetulkan.',
            ],
        ],
    ],
    '4.9.75' => [
        'en' => [
            'summary' => 'Retire the unused PayPal checkout integration and remove its abandoned SDK dependency graph.',
            'changes' => [
                'Remove the PayPal gateway, response handler, service registration, and checkout approval flow.',
                'Remove PayPal configuration, validation, admin settings, translations, installer defaults, and storefront assets.',
                'Remove the abandoned paypal/paypal-checkout-sdk and paypal/paypalhttp packages from Composer and the locked dependency graph.',
                'Rebuild production assets without the PayPal browser SDK or checkout button implementation.',
                'Add regression coverage that prevents the retired PayPal SDK packages from being reintroduced silently.',
            ],
        ],
        'ms' => [
            'summary' => 'Tamatkan integrasi checkout PayPal yang tidak digunakan dan buang graph dependency SDK yang abandoned.',
            'changes' => [
                'Buang gateway PayPal, response handler, pendaftaran service, dan aliran kelulusan checkout.',
                'Buang konfigurasi, validasi, tetapan admin, terjemahan, default installer, dan aset storefront PayPal.',
                'Buang pakej abandoned paypal/paypal-checkout-sdk dan paypal/paypalhttp daripada Composer serta graph dependency yang dikunci.',
                'Bina semula aset production tanpa browser SDK atau implementasi butang checkout PayPal.',
                'Tambah liputan regresi bagi menghalang pakej SDK PayPal yang telah ditamatkan daripada dimasukkan semula secara senyap.',
            ],
        ],
    ],
    '4.9.74' => [
        'en' => [
            'summary' => 'Upgrade to the patched Laravel 12 stack with compatible authentication, module, data-table, and nested-collection packages.',
            'changes' => [
                'Upgrade Laravel to 12.64.0 to close the framework email-validation CRLF and temporary signed-URL advisories.',
                'Upgrade Sentinel, Laravel Modules, Yajra DataTables, and NestableCollection to their Laravel 12-compatible major releases.',
                'Require PHP 8.3.1 through 8.4.x and document the production platform check required before deployment.',
                'Add regression coverage for the framework and upgraded package graph while keeping all 507 application routes bootable.',
                'Remove PHP 8.5 GD cleanup deprecations and reduce Composer audit output to one low-severity JWT advisory constrained by Socialite.',
            ],
        ],
        'ms' => [
            'summary' => 'Naik taraf kepada stack Laravel 12 yang telah ditampal bersama package authentication, module, data-table, dan nested collection yang serasi.',
            'changes' => [
                'Naik taraf Laravel kepada 12.64.0 untuk menutup advisory CRLF validasi e-mel dan temporary signed URL pada framework.',
                'Naik taraf Sentinel, Laravel Modules, Yajra DataTables, dan NestableCollection kepada major release yang serasi dengan Laravel 12.',
                'Wajibkan PHP 8.3.1 hingga 8.4.x dan dokumentasikan semakan platform production sebelum deployment.',
                'Tambah liputan regresi bagi framework serta graph package baharu sambil memastikan semua 507 route aplikasi boleh diboot.',
                'Buang deprecation pembersihan GD pada PHP 8.5 dan kurangkan audit Composer kepada satu advisory JWT tahap rendah yang dikunci oleh Socialite.',
            ],
        ],
    ],
    '4.9.73' => [
        'en' => [
            'summary' => 'Refresh vulnerable dependencies and make production installs reproducible with a committed Composer lock file.',
            'changes' => [
                'Update 28 patch-compatible Composer packages, including AWS SDK, Dompdf, Guzzle, PhpSpreadsheet, phpseclib, PsySH, and Symfony components.',
                'Use the patched Symfony Cache 6.4 LTS release while preserving compatibility with the existing PSR Cache v2 adapters.',
                'Reduce Composer security advisories from 42 records across 14 packages to 4 records across 2 packages.',
                'Commit composer.lock so production deployments install the same reviewed dependency graph instead of resolving new versions at deploy time.',
                'Document the remaining Laravel 12, JWT/Socialite, and abandoned PayPal SDK migration work without forcing incompatible major upgrades.',
            ],
        ],
        'ms' => [
            'summary' => 'Kemas kini dependency terdedah dan jadikan pemasangan production reproducible melalui Composer lock file yang disimpan dalam Git.',
            'changes' => [
                'Kemas kini 28 pakej Composer yang serasi pada tahap patch, termasuk AWS SDK, Dompdf, Guzzle, PhpSpreadsheet, phpseclib, PsySH, dan komponen Symfony.',
                'Gunakan Symfony Cache 6.4 LTS yang telah ditampal sambil mengekalkan keserasian dengan adapter PSR Cache v2 sedia ada.',
                'Kurangkan advisory keselamatan Composer daripada 42 rekod merentas 14 pakej kepada 4 rekod merentas 2 pakej.',
                'Simpan composer.lock dalam Git supaya deployment production memasang dependency graph yang sama dan telah disemak.',
                'Dokumenkan baki migrasi Laravel 12, JWT/Socialite, dan PayPal SDK yang abandoned tanpa memaksa upgrade major yang tidak serasi.',
            ],
        ],
    ],
    '4.9.72' => [
        'en' => [
            'summary' => 'Add automated regression coverage, read-only production benchmarks, and a database-enforced booking-slot invariant.',
            'changes' => [
                'Install PHPUnit 11 and convert the existing Pest-style checks into an executable Laravel-compatible test suite.',
                'Cover payment replay idempotency, calendar-token expiry and revocation, private document access, consultation security, and booking-slot collisions.',
                'Add a bounded read-only benchmark command with representative order, category, and appointment queries plus JSON EXPLAIN summaries.',
                'Create a normalized shared slot ledger with a unique database key across checkout orders and manual treatment bookings.',
                'Keep the slot ledger synchronized through MySQL triggers, reject unsafe legacy duplicates during migration, and return a friendly conflict response for concurrent bookings.',
            ],
        ],
        'ms' => [
            'summary' => 'Tambah liputan regresi automatik, benchmark production read-only, dan invariant slot tempahan di peringkat database.',
            'changes' => [
                'Pasang PHPUnit 11 dan tukar semakan gaya Pest sedia ada kepada test suite serasi Laravel yang boleh dijalankan.',
                'Lindungi idempotency replay pembayaran, expiry dan revocation token kalendar, akses dokumen private, keselamatan konsultasi, serta collision slot tempahan.',
                'Tambah command benchmark read-only terkawal untuk query pesanan, kategori, dan temujanji bersama ringkasan JSON EXPLAIN.',
                'Cipta ledger slot ternormalisasi dengan unique key database merentas pesanan checkout dan tempahan rawatan manual.',
                'Sinkronkan ledger melalui trigger MySQL, hentikan migration jika data lama bercanggah, dan pulangkan respons mesra bagi tempahan serentak.',
            ],
        ],
    ],
    '4.9.71' => [
        'en' => [
            'summary' => 'Add explicit, race-safe saved-address controls to checkout without overwriting existing addresses.',
            'changes' => [
                'Let signed-in customers reuse saved or recent billing details and explicitly save new billing or shipping addresses.',
                'Keep existing addresses intact and change the default address only when selected, or when the customer has no default yet.',
                'Persist saved addresses inside the order transaction with customer row locking and atomic default-address updates.',
                'Prevent conflicting billing and shipping default selections, validate address field types and lengths, and escape displayed address data.',
                'Improve the checkout address interface with bilingual labels, responsive save options, and synchronized country, state, city, and postcode fields.',
            ],
        ],
        'ms' => [
            'summary' => 'Tambah kawalan alamat tersimpan yang explicit dan tahan race condition tanpa menimpa alamat sedia ada.',
            'changes' => [
                'Benarkan pelanggan log masuk menggunakan semula alamat atau maklumat bil terkini dan menyimpan alamat bil atau penghantaran baharu secara explicit.',
                'Kekalkan alamat lama dan ubah alamat lalai hanya apabila dipilih, atau apabila pelanggan belum mempunyai alamat lalai.',
                'Simpan alamat dalam transaction pesanan dengan customer row lock dan kemas kini alamat lalai secara atomic.',
                'Cegah pilihan default bil dan penghantaran yang bercanggah, sahkan jenis serta panjang medan, dan escape paparan data alamat.',
                'Kemaskan antaramuka alamat checkout dengan label dwibahasa, pilihan simpan responsif, serta sinkronisasi negara, negeri, bandar, dan poskod.',
            ],
        ],
    ],
    '4.9.70' => [
        'en' => [
            'summary' => 'Harden checkout, bookings, private documents, public tokens, queues, and database performance.',
            'changes' => [
                'Enforce address ownership and one atomic default address per customer to prevent IDOR and concurrent duplicate records.',
                'Finalize payments and booking changes inside transactions with row locks, replay protection, and after-commit events.',
                'Move payment proofs and generated order PDFs to private storage with expiring signed access and a legacy-document migration command.',
                'Hash, encrypt, expire, and rotate consultation and beautician calendar tokens while removing customer details from calendar feeds.',
                'Reduce availability and product query overhead, make report filters index-friendly, and add targeted production-safe indexes and foreign keys.',
                'Queue email and WhatsApp work with database-backed deduplication, retries, cache invalidation, throttling, and safer Blade output escaping.',
            ],
        ],
        'ms' => [
            'summary' => 'Perketat checkout, tempahan, dokumen private, token awam, queue, dan prestasi database.',
            'changes' => [
                'Kuatkuasakan pemilikan alamat dan satu alamat lalai atomic bagi setiap pelanggan untuk mencegah IDOR serta rekod pendua serentak.',
                'Selesaikan pembayaran dan perubahan tempahan dalam transaction dengan row lock, perlindungan replay, dan event selepas commit.',
                'Pindahkan bukti bayaran dan PDF pesanan ke storan private dengan akses signed bertempoh serta command migrasi dokumen legacy.',
                'Hash, enkripsi, tamatkan, dan putar token konsultasi serta kalendar beautician sambil membuang butiran pelanggan daripada calendar feed.',
                'Kurangkan beban query availability dan produk, jadikan filter laporan mesra index, serta tambah index dan foreign key production-safe.',
                'Queue email dan WhatsApp dengan deduplikasi database, retry, cache invalidation, throttling, dan escaping output Blade yang lebih selamat.',
            ],
        ],
    ],
    '4.9.69' => [
        'en' => [
            'summary' => 'Add conditional consultation fields and harden medical records for privacy, performance, and auditability.',
            'changes' => [
                'Let admins add inline follow-up fields that appear only when a customer gives the configured answer.',
                'Encrypt consultation answers and legal snapshots, and move signatures and generated PDFs to private storage.',
                'Reduce consultation list queries with encrypted context snapshots, lean column selection, pagination, and targeted database indexes.',
                'Cache immutable PDFs, support background PDF generation, and record customer and admin access to completed consultations.',
                'Improve bilingual follow-up labels, conditional validation, translation cache refreshes, sensitive-page headers, and request throttling.',
            ],
        ],
        'ms' => [
            'summary' => 'Tambah medan konsultasi bersyarat dan perketat rekod perubatan untuk privasi, prestasi, serta jejak audit.',
            'changes' => [
                'Benarkan admin menambah medan susulan terus yang hanya muncul apabila pelanggan memberikan jawapan yang ditetapkan.',
                'Enkripsi jawapan konsultasi dan snapshot dokumen undang-undang, serta pindahkan tandatangan dan PDF ke storan private.',
                'Kurangkan query senarai konsultasi melalui context snapshot terenkripsi, pemilihan kolum ringkas, pagination, dan index database khusus.',
                'Cache PDF yang kekal, sokong penjanaan PDF melalui queue, dan rekod akses pelanggan serta admin kepada konsultasi lengkap.',
                'Kemaskan label susulan dwibahasa, validasi bersyarat, refresh cache terjemahan, header halaman sensitif, dan rate limiting.',
            ],
        ],
    ],
    '4.9.68' => [
        'en' => [
            'summary' => 'Add secure consultation forms with medical body mapping, signed records, PDF downloads, and editable legal policies.',
            'changes' => [
                'Let beauticians send a secure consultation link from a confirmed treatment booking and let customers complete it from their account.',
                'Add numbered bilingual medical questions, an interactive anatomy body map, customer signature, and matching admin and customer PDFs.',
                'Show purchased treatment, appointment, branch, beautician, and order information with every consultation record.',
                'Add database-managed Terms & Conditions and Privacy Policy pages with immutable policy snapshots for signed submissions.',
                'Strengthen customer access, answer allowlists, signature validation, permissions, error handling, and consultation service architecture.',
            ],
        ],
        'ms' => [
            'summary' => 'Tambah borang konsultasi selamat dengan peta anatomi, rekod bertandatangan, muat turun PDF, dan polisi undang-undang yang boleh diedit.',
            'changes' => [
                'Benarkan beautician menghantar pautan konsultasi selamat daripada tempahan rawatan dan pelanggan melengkapkannya melalui akaun sendiri.',
                'Tambah soalan perubatan dwibahasa bernombor, peta anatomi interaktif, tandatangan pelanggan, serta PDF admin dan pelanggan yang seragam.',
                'Paparkan rawatan dibeli, janji temu, cawangan, beautician, dan maklumat pesanan pada setiap rekod konsultasi.',
                'Tambah halaman Terma & Syarat dan Dasar Privasi berasaskan database bersama snapshot polisi kekal untuk submission bertandatangan.',
                'Perketat akses pelanggan, pilihan jawapan, validasi tandatangan, permission, pengendalian ralat, dan struktur servis konsultasi.',
            ],
        ],
    ],
    '4.9.67' => [
        'en' => [
            'summary' => 'Replace technical server errors with friendly, branded, and accessible error pages.',
            'changes' => [
                'Add dedicated pages for 403, 404, 405, 419, 429, 500, and 503 errors with safe fallback pages.',
                'Show clear recovery actions for admin and storefront visitors without exposing technical details.',
                'Support English and Malay error messages with responsive and keyboard-accessible layouts.',
                'Return the correct HTTP 404 status for missing storefront pages instead of an incorrect successful response.',
            ],
        ],
        'ms' => [
            'summary' => 'Gantikan ralat teknikal server dengan halaman ralat yang mesra, berjenama, dan mudah diakses.',
            'changes' => [
                'Tambah halaman khusus untuk ralat 403, 404, 405, 419, 429, 500, dan 503 bersama halaman fallback yang selamat.',
                'Paparkan tindakan pemulihan yang jelas untuk pengguna admin dan storefront tanpa mendedahkan maklumat teknikal.',
                'Sokong mesej ralat Bahasa Inggeris dan Bahasa Melayu dengan susun atur responsif serta mesra papan kekunci.',
                'Kembalikan status HTTP 404 yang betul untuk halaman storefront yang tidak ditemui.',
            ],
        ],
    ],
    '4.9.66' => [
        'en' => [
            'summary' => 'Link customers on admin order pages directly to their user profiles.',
            'changes' => [
                'Make the customer name and avatar clickable when the order is linked to an existing user.',
                'Use FleetCart access permissions so authorized admins can reliably see the profile link.',
                'Keep guest orders and deleted customer accounts as safe non-clickable text.',
                'Add clear hover and keyboard-focus feedback to the customer profile link.',
            ],
        ],
        'ms' => [
            'summary' => 'Pautkan customer pada halaman order admin terus ke profil user mereka.',
            'changes' => [
                'Jadikan nama dan avatar customer boleh diklik apabila order dipautkan kepada user sedia ada.',
                'Gunakan permission FleetCart supaya admin yang dibenarkan dapat melihat pautan profil dengan konsisten.',
                'Kekalkan order tetamu dan akaun customer yang telah dipadam sebagai teks selamat tanpa pautan.',
                'Tambah maklum balas hover dan fokus papan kekunci yang jelas pada pautan profil customer.',
            ],
        ],
    ],
    '4.9.65' => [
        'en' => [
            'summary' => 'Simplify beautician creation and improve the edit profile layout for faster, clearer staff administration.',
            'changes' => [
                'Replace the multi-step create experience with one clear form that keeps all essential fields visible.',
                'Create the secure portal login automatically and remove unnecessary account choices from the create form.',
                'Clarify branch availability, checkout visibility, validation feedback, and automatic booking-list position.',
                'Improve the profile photo picker and use the full edit-page width with responsive information cards.',
            ],
        ],
        'ms' => [
            'summary' => 'Permudahkan penciptaan beautician dan kemaskan susun atur profil edit untuk urusan staf yang lebih cepat dan jelas.',
            'changes' => [
                'Gantikan proses create berbilang langkah dengan satu borang jelas yang memaparkan semua medan penting.',
                'Cipta login portal selamat secara automatik dan buang pilihan akaun yang tidak diperlukan daripada borang create.',
                'Jelaskan ketersediaan cawangan, paparan checkout, maklum balas validasi, dan kedudukan automatik dalam senarai tempahan.',
                'Kemaskan pemilih gambar profil dan gunakan keseluruhan lebar halaman edit dengan kad maklumat responsif.',
            ],
        ],
    ],
    '4.9.64' => [
        'en' => [
            'summary' => 'Modernise beautician portal with SaaS job sheet UI, professional performance stats, and portal-only navigation fixes.',
            'changes' => [
                'Redirect beautician login to the portal dashboard and allow self-access to beauticians/{id}/portal routes.',
                'Redesign job sheet with hero, KPI performance panel, pipeline bar, and today sidebar.',
                'Keep full calendar, profile, and account links inside the beautician portal instead of full admin.',
                'Add an improved admin preview banner when viewing a beautician portal as admin.',
            ],
        ],
        'ms' => [
            'summary' => 'Modenkan portal beautician dengan UI job sheet SaaS, statistik prestasi profesional, dan pembetulan navigasi portal.',
            'changes' => [
                'Alihkan log masuk beautician ke dashboard portal dan benarkan akses sendiri ke laluan beauticians/{id}/portal.',
                'Reka semula job sheet dengan hero, panel prestasi KPI, bar pipeline, dan sidebar hari ini.',
                'Kekalkan kalendar penuh, profil, dan akaun dalam portal beautician tanpa menu admin penuh.',
                'Tambah banner pratonton admin yang lebih jelas apabila melihat portal beautician sebagai admin.',
            ],
        ],
    ],
    '4.9.52' => [
        'en' => [
            'summary' => 'Improve WordPress customer import command error messages and file path resolution.',
            'changes' => [
                'Reject the placeholder /path/to/file.sql and show clearer usage examples when the SQL dump is missing.',
                'Resolve relative paths from project root and storage/app for easier server imports.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki mesej ralat dan penyelesaian path pada arahan import pelanggan WordPress.',
            'changes' => [
                'Tolak placeholder /path/to/file.sql dan paparkan contoh penggunaan yang lebih jelas apabila fail SQL tiada.',
                'Selesaikan path relatif dari root projek dan storage/app untuk import di server dengan lebih mudah.',
            ],
        ],
    ],
    '4.9.51' => [
        'en' => [
            'summary' => 'Import WordPress/WooCommerce customers with preserved login passwords.',
            'changes' => [
                'Add user:import-wordpress-customers artisan command to migrate WP user SQL dumps as FleetCart customers.',
                'Support WordPress phpass and bcrypt password hashes so imported customers can sign in with existing passwords.',
                'Assign Customer role and activate imported accounts automatically.',
            ],
        ],
        'ms' => [
            'summary' => 'Import pelanggan WordPress/WooCommerce dengan kata laluan login yang dikekalkan.',
            'changes' => [
                'Tambah arahan artisan user:import-wordpress-customers untuk migrasi dump SQL pengguna WP sebagai pelanggan FleetCart.',
                'Sokong hash kata laluan phpass dan bcrypt WordPress supaya pelanggan diimport boleh log masuk dengan kata laluan sedia ada.',
                'Berikan peranan Customer dan aktifkan akaun yang diimport secara automatik.',
            ],
        ],
    ],
    '4.9.50' => [
        'en' => [
            'summary' => 'Modernise the admin page editor and About Us page with layout-safe HTML editing.',
            'changes' => [
                'Redesign the admin page editor with hub header, sticky save bar, Visual/Code toggle, and SEO sidebar preview.',
                'Preserve custom page HTML (div, section, classes) in CKEditor via General HTML Support and HTML5 sanitiser rules.',
                'Add a modern About Us layout with dynamic spa branch locations and balanced image/content sections.',
                'Fix OG share image preview in the page SEO panel with proper 1200×630 aspect ratio.',
            ],
        ],
        'ms' => [
            'summary' => 'Modenkan editor halaman admin dan halaman Tentang Kami dengan suntingan HTML yang selamat.',
            'changes' => [
                'Reka semula editor halaman admin dengan header hab, bar simpan melekit, toggle Visual/Kod, dan pratonton SEO.',
                'Kekalkan HTML halaman tersuai (div, section, class) dalam CKEditor melalui General HTML Support dan peraturan HTML5.',
                'Tambah susun atur Tentang Kami moden dengan lokasi cawangan spa dinamik dan bahagian gambar/kandungan seimbang.',
                'Baiki pratonton imej kongsi OG dalam panel SEO halaman dengan nisbah 1200×630 yang betul.',
            ],
        ],
    ],
    '4.9.47' => [
        'en' => [
            'summary' => 'Add CHIP e-wallet and DuitNow checkout methods with an improved admin setup panel.',
            'changes' => [
                'Add chip_ewallet and chip_duitnow as separate CHIP Collect checkout options with surcharge and whitelist support.',
                'Replace the CHIP settings branding sidebar with a full-width setup summary (status, checklist, webhook URL, quick links).',
                'Improve CHIP admin tab layout to a single-column form with stacked summary sections.',
            ],
        ],
        'ms' => [
            'summary' => 'Tambah kaedah checkout CHIP e-wallet dan DuitNow dengan panel persediaan admin yang lebih baik.',
            'changes' => [
                'Tambah chip_ewallet dan chip_duitnow sebagai pilihan checkout CHIP Collect berasingan dengan caj dan whitelist.',
                'Ganti sidebar branding CHIP dengan ringkasan persediaan lebar penuh (status, checklist, URL webhook, pautan pantas).',
                'Baiki susun atur tab CHIP admin kepada borang satu kolum dengan bahagian ringkasan bertindan.',
            ],
        ],
    ],
    '4.9.46' => [
        'en' => [
            'summary' => 'Add gift voucher admin hub with page content, design, and settings tabs.',
            'changes' => [
                'Introduce a Gift voucher hub with Submissions, Page content, Page design, and Settings tabs.',
                'Let admins edit send-gift page copy, accent colours, and visual effects with live previews.',
                'Wire the public /send-gift page to hub settings for copy, theme accents, and voucher configuration.',
            ],
        ],
        'ms' => [
            'summary' => 'Tambah hab admin baucar hadiah dengan tab kandungan, reka bentuk, dan tetapan.',
            'changes' => [
                'Perkenalkan Hab baucar hadiah dengan tab Penyerahan, Kandungan halaman, Reka bentuk halaman, dan Tetapan.',
                'Benarkan admin sunting teks halaman send-gift, warna aksen, dan kesan visual dengan pratonton langsung.',
                'Sambungkan halaman awam /send-gift kepada tetapan hab untuk teks, aksen tema, dan konfigurasi baucar.',
            ],
        ],
    ],
    '4.9.45' => [
        'en' => [
            'summary' => 'Add scheduled promo countdown on product pages and improve special price admin tools.',
            'changes' => [
                'Show a cosmetic promo countdown on the storefront when a variant special price has start or end dates.',
                'Support datetime special price schedules in admin, including bulk edit for all variants.',
                'Fix special price date saving, variant persistence, and add a View Live Product button on product edit.',
            ],
        ],
        'ms' => [
            'summary' => 'Tambah countdown promo berjadual pada halaman produk dan baiki alat harga istimewa admin.',
            'changes' => [
                'Paparkan countdown promo estetik di storefront apabila variant ada tarikh mula atau tamat harga istimewa.',
                'Sokong jadual harga istimewa dengan masa dalam admin, termasuk suntingan pukal untuk semua variant.',
                'Baiki simpanan tarikh harga istimewa, kekal variant, dan tambah butang Lihat Produk Langsung pada sunting produk.',
            ],
        ],
    ],
    '4.9.44' => [
        'en' => [
            'summary' => 'Improve CRM embedded calendar month navigation and slide transitions.',
            'changes' => [
                'Show month/year navigation on the reservations dashboard embedded calendar.',
                'Slide the calendar left or right when changing months instead of flashing a full reload.',
                'Keep day agenda interactions working during animated month changes.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki navigasi bulan dan animasi slide pada kalendar CRM.',
            'changes' => [
                'Paparkan navigasi bulan/tahun pada kalendar terbenam dashboard tempahan.',
                'Gelongsorkan kalendar ke kiri atau kanan apabila menukar bulan dan bukannya refresh penuh.',
                'Kekalkan interaksi agenda hari berfungsi semasa animasi tukar bulan.',
            ],
        ],
    ],
    '4.9.43' => [
        'en' => [
            'summary' => 'Show order, payment, and treatment status on the customer account orders screens.',
            'changes' => [
                'Add a Treatment status column and badges to Recent Orders, My Orders, and order detail pages.',
                'Load treatment booking data with customer orders and align badge colors with admin status chips.',
                'Add English and Bahasa Malaysia labels for the three separate statuses.',
            ],
        ],
        'ms' => [
            'summary' => 'Paparkan status pesanan, bayaran, dan rawatan pada skrin pesanan akaun pelanggan.',
            'changes' => [
                'Tambah lajur dan lencana Status Rawatan pada Pesanan Terkini, Pesanan Saya, dan halaman butiran pesanan.',
                'Muat data tempahan rawatan bersama pesanan pelanggan dan selaraskan warna lencana dengan chip status admin.',
                'Tambah label Inggeris dan Bahasa Malaysia untuk tiga status berasingan.',
            ],
        ],
    ],
    '4.9.42' => [
        'en' => [
            'summary' => 'Send WhatsApp OTP immediately instead of queuing it.',
            'changes' => [
                'Bypass the outbound WhatsApp queue for OTP login codes so customers receive codes right away.',
                'Show a real API error when OneSender fails instead of a false “OTP sent” success message.',
            ],
        ],
        'ms' => [
            'summary' => 'Hantar OTP WhatsApp serta-merta tanpa giliran.',
            'changes' => [
                'Langkau giliran WhatsApp keluar untuk kod OTP log masuk supaya pelanggan terima kod dengan segera.',
                'Paparkan ralat API sebenar apabila OneSender gagal dan bukannya mesej palsu “OTP dihantar”.',
            ],
        ],
    ],
    '4.9.41' => [
        'en' => [
            'summary' => 'Fix admin order Actions dropdown visibility and print menu behavior.',
            'changes' => [
                'Stop clipping the Actions dropdown menu caused by overflow hidden on the order hero card.',
                'Let Print, Receipt, and Back use native link navigation instead of window.open so popup blockers and embedded browsers work reliably.',
                'Sync dropdown aria-expanded when closing the Actions menu after a selection.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki keterlihatan menu Tindakan dan kelakuan cetak pada halaman pesanan admin.',
            'changes' => [
                'Hentikan pemotongan menu Tindakan yang disebabkan overflow hidden pada kad hero pesanan.',
                'Benarkan Cetak, Resit, dan Kembali menggunakan navigasi pautan asli dan bukannya window.open supaya penyekat popup dan pelayar terbenam berfungsi dengan baik.',
                'Selaraskan aria-expanded dropdown apabila menu Tindakan ditutup selepas pilihan dibuat.',
            ],
        ],
    ],
    '4.9.40' => [
        'en' => [
            'summary' => 'Modern light redesign of the admin order details page.',
            'changes' => [
                'Rebuild the order hero with a floating card, labeled status panel, and prominent total.',
                'Replace heavy status boxes with a compact command bar and inline status strip.',
                'Refresh cards, tables, sidebar, and action buttons with a lighter modern visual style.',
                'Keep status chips in sync when admins change order, payment, or treatment status.',
            ],
        ],
        'ms' => [
            'summary' => 'Reka bentuk moden dan ringan untuk halaman butiran pesanan admin.',
            'changes' => [
                'Bina semula hero pesanan dengan kad terapung, panel status berlabel, dan jumlah yang lebih menonjol.',
                'Ganti kotak status berat dengan bar arahan padat dan jalur status sebaris.',
                'Segarkan kad, jadual, sidebar, dan butang tindakan dengan gaya visual moden yang lebih ringan.',
                'Kekalkan chip status selaras apabila admin menukar status pesanan, bayaran, atau rawatan.',
            ],
        ],
    ],
    '4.9.39' => [
        'en' => [
            'summary' => 'Separate order, payment, and treatment statuses; improve admin order page layout.',
            'changes' => [
                'Add a dedicated Treatment Status control on the order page and decouple it from order status and the job sheet pipeline.',
                'Add Payment Status and Treatment Status columns to Google Sheets sync.',
                'Reorganize the admin order page: remove duplicate account info, move appointment and Google Sheets to the sidebar, and show payment breakdown under items.',
                'Fix raw translation keys for treatment status labels and polish Malay admin copy.',
            ],
        ],
        'ms' => [
            'summary' => 'Pisahkan status pesanan, bayaran, dan rawatan; baiki susun atur halaman pesanan admin.',
            'changes' => [
                'Tambah kawalan Status Rawatan khusus pada halaman pesanan dan pisahkan daripada status pesanan serta pipeline job sheet.',
                'Tambah lajur Status Bayaran dan Status Rawatan pada penyegerakan Google Sheets.',
                'Susun semula halaman pesanan admin: buang maklumat akaun berulang, pindahkan temujanji dan Google Sheets ke sidebar, dan paparkan pecahan bayaran di bawah item.',
                'Baiki kunci terjemahan mentah untuk label status rawatan dan perhalusi salinan admin Bahasa Malaysia.',
            ],
        ],
    ],
    '4.9.38' => [
        'en' => [
            'summary' => 'Fix WhatsApp payment proof attachments and add admin orders bulk delete.',
            'changes' => [
                'Publish bank transfer proof files on a WhatsApp-safe public URL so group alerts include the receipt image.',
                'Add bulk delete to the admin orders table, including permanent delete in archived view.',
                'Clarify Google Sheets sync issue filter labels and remove the redundant archived orders banner.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki lampiran bukti bayaran WhatsApp dan tambah padam pukal pesanan admin.',
            'changes' => [
                'Terbitkan fail bukti pemindahan bank pada URL awam selamat WhatsApp supaya amaran kumpulan menyertakan imej resit.',
                'Tambah padam pukal pada jadual pesanan admin, termasuk padam kekal dalam paparan arkib.',
                'Jelaskan label penapis isu sync Google Sheets dan buang banner pesanan diarkibkan yang berlebihan.',
            ],
        ],
    ],
    '4.9.37' => [
        'en' => [
            'summary' => 'Show bank transfer payment proof on customer order pages.',
            'changes' => [
                'Display uploaded payment proof in the Payment details section on account order view.',
                'Show inline image preview or PDF download link for bank transfer orders.',
            ],
        ],
        'ms' => [
            'summary' => 'Paparkan bukti bayaran pemindahan bank pada halaman pesanan pelanggan.',
            'changes' => [
                'Paparkan bukti bayaran yang dimuat naik dalam bahagian Butiran Bayaran pada paparan pesanan akaun.',
                'Tunjuk preview imej inline atau pautan muat turun PDF untuk pesanan pemindahan bank.',
            ],
        ],
    ],
    '4.9.36' => [
        'en' => [
            'summary' => 'Fix Bank Transfer checkout 500 error in production.',
            'changes' => [
                'Redirect offline payments directly after checkout instead of calling complete with an undefined order ID.',
                'Isolate OrderPlaced listener failures so WhatsApp proof alerts cannot break checkout.',
                'Validate order ID on checkout complete and harden bank transfer proof upload error handling.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki ralat 500 checkout Pemindahan Bank dalam production.',
            'changes' => [
                'Alihkan bayaran offline terus selepas checkout tanpa memanggil complete dengan ID pesanan undefined.',
                'Asingkan kegagalan listener OrderPlaced supaya amaran WhatsApp bukti tidak pecahkan checkout.',
                'Sahkan ID pesanan pada checkout complete dan perkukuh pengendalian ralat muat naik bukti pemindahan bank.',
            ],
        ],
    ],
    '4.9.35' => [
        'en' => [
            'summary' => 'Bank transfer payment proof WhatsApp alerts and inline admin receipt preview.',
            'changes' => [
                'Send uploaded bank transfer proof to a dedicated WhatsApp group after checkout.',
                'Show payment receipt inline under Payment Method on the admin order page.',
                'Harden payment proof file storage and require proof on bank transfer checkout.',
            ],
        ],
        'ms' => [
            'summary' => 'Amaran WhatsApp bukti bayaran pemindahan bank dan preview resit inline dalam admin.',
            'changes' => [
                'Hantar bukti bayaran pemindahan bank ke kumpulan WhatsApp khusus selepas checkout.',
                'Paparkan resit bayaran terus di bawah Kaedah Bayaran pada halaman pesanan admin.',
                'Perkukuh penyimpanan fail bukti bayaran dan wajibkan bukti semasa checkout pemindahan bank.',
            ],
        ],
    ],
    '4.9.34' => [
        'en' => [
            'summary' => 'Bank Transfer checkout now accepts customer payment proof uploads.',
            'changes' => [
                'Add required payment proof upload when customers choose Bank Transfer at checkout.',
                'Store proof files on the order and show download links in admin and customer order views.',
            ],
        ],
        'ms' => [
            'summary' => 'Checkout pemindahan bank kini menerima muat naik bukti bayaran pelanggan.',
            'changes' => [
                'Tambah muat naik bukti bayaran wajib apabila pelanggan pilih Pemindahan Bank semasa checkout.',
                'Simpan fail bukti pada pesanan dan paparkan pautan muat turun dalam admin dan paparan pesanan pelanggan.',
            ],
        ],
    ],
    '4.9.33' => [
        'en' => [
            'summary' => 'Fix raw translation keys on Facebook and Google social login settings tabs.',
            'changes' => [
                'Add missing button label translations for Facebook and Google login settings.',
                'Refresh setting module translation cache so admin settings pages show proper labels.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki kunci terjemahan mentah pada tab tetapan log masuk Facebook dan Google.',
            'changes' => [
                'Tambah terjemahan label butang yang hilang untuk tetapan log masuk Facebook dan Google.',
                'Segarkan cache terjemahan modul tetapan supaya halaman admin memaparkan label dengan betul.',
            ],
        ],
    ],
    '4.9.32' => [
        'en' => [
            'summary' => 'Google reCAPTCHA v2/v3 option with login protection and v3 score validation.',
            'changes' => [
                'Choose reCAPTCHA v2 checkbox or v3 invisible score-based mode in Settings.',
                'v3 validates score threshold server-side and works on login, register, contact, and reviews.',
                'Fix Google reCAPTCHA settings tab dropdown error and update admin labels.',
            ],
        ],
        'ms' => [
            'summary' => 'Pilihan Google reCAPTCHA v2/v3 dengan perlindungan log masuk dan pengesahan skor v3.',
            'changes' => [
                'Pilih mod reCAPTCHA v2 kotak semak atau v3 skor tidak kelihatan dalam Tetapan.',
                'v3 sahkan ambang skor di server dan berfungsi pada log masuk, daftar, hubungi, dan ulasan.',
                'Baiki ralat dropdown tab tetapan Google reCAPTCHA dan kemas kini label admin.',
            ],
        ],
    ],
    '4.9.31' => [
        'en' => [
            'summary' => 'Google reCAPTCHA now protects customer, admin, and checkout login forms.',
            'changes' => [
                'Add reCAPTCHA widget to storefront login, admin login, and checkout inline sign-in.',
                'Validate captcha on all email/password login requests when reCAPTCHA is enabled.',
                'Update settings copy to list login alongside registration, reviews, and contact.',
            ],
        ],
        'ms' => [
            'summary' => 'Google reCAPTCHA kini melindungi borang log masuk pelanggan, admin, dan checkout.',
            'changes' => [
                'Tambah widget reCAPTCHA pada log masuk storefront, admin, dan sign-in inline checkout.',
                'Sahkan captcha pada semua permintaan log masuk e-mel/kata laluan apabila reCAPTCHA diaktifkan.',
                'Kemas kini teks tetapan untuk sertakan log masuk bersama pendaftaran, ulasan, dan hubungi.',
            ],
        ],
    ],
    '4.9.30' => [
        'en' => [
            'summary' => 'Professional customer invoice email with embedded logo and PDF attachments.',
            'changes' => [
                'Redesign invoice email with item table and full payment summary breakdown.',
                'Fix mail logo by embedding the image inline instead of broken localhost URLs.',
                'Attach invoice and receipt PDFs to every customer invoice email.',
            ],
        ],
        'ms' => [
            'summary' => 'E-mel invois pelanggan profesional dengan logo terbenam dan lampiran PDF.',
            'changes' => [
                'Reka bentuk semula e-mel invois dengan jadual item dan ringkasan bayaran lengkap.',
                'Baiki logo e-mel dengan embed imej terus dan bukan URL localhost yang rosak.',
                'Lampirkan PDF invois dan resit pada setiap e-mel invois pelanggan.',
            ],
        ],
    ],
    '4.9.29' => [
        'en' => [
            'summary' => 'Google sync shows product options; calendar re-sync recreates deleted events without duplicates.',
            'changes' => [
                'Google Sheets and Calendar treatment lines include selected variations and product options.',
                'Sync all appointments checks Google Calendar first — skips existing events, recreates deleted ones.',
                'Bulk sync result shows created, already on calendar, and failed counts.',
            ],
        ],
        'ms' => [
            'summary' => 'Sync Google tunjuk pilihan produk; sync semula kalendar cipta semula acara dipadam tanpa duplicate.',
            'changes' => [
                'Google Sheets dan Calendar sertakan variasi dan pilihan produk yang dipilih pelanggan.',
                'Sync semua temujanji semak Google Calendar dahulu — langkau acara sedia ada, cipta semula yang dipadam.',
                'Hasil sync pukal tunjuk bilangan dicipta, sudah ada di kalendar, dan gagal.',
            ],
        ],
    ],
    '4.9.26' => [
        'en' => [
            'summary' => 'Fix Google Calendar test connection accessRole API error.',
            'changes' => [
                'Resolve writer access via calendarList/ACL instead of invalid calendars.get accessRole field.',
                'Test connection no longer shows “Invalid field selection accessRole”.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki ralat API accessRole pada ujian sambungan Google Calendar.',
            'changes' => [
                'Semak akses writer melalui calendarList/ACL dan bukan medan accessRole pada calendars.get.',
                'Uji sambungan tidak lagi memaparkan “Invalid field selection accessRole”.',
            ],
        ],
    ],
    '4.9.25' => [
        'en' => [
            'summary' => 'Google Calendar sync diagnostics, stats, and open calendar button.',
            'changes' => [
                'Test connection now requires writer access (not view-only) before sync can succeed.',
                'Sync all appointments shows per-order error messages when creation fails.',
                'Add sync stats (events created / waiting) and Open Google Calendar button.',
            ],
        ],
        'ms' => [
            'summary' => 'Diagnostik sync Google Calendar, statistik, dan butang buka kalendar.',
            'changes' => [
                'Uji sambungan kini memerlukan akses writer (bukan lihat sahaja) sebelum sync berjaya.',
                'Sync semua temujanji memaparkan ralat setiap pesanan apabila cipta acara gagal.',
                'Tambah statistik sync (acara dicipta / menunggu) dan butang Buka Google Calendar.',
            ],
        ],
    ],
    '4.9.24' => [
        'en' => [
            'summary' => 'Sync all appointments button for Google Calendar backfill.',
            'changes' => [
                'Add Sync all appointments on Google Calendar settings with chunked progress.',
                'Add google-calendar:backfill artisan command for server-side backfill.',
                'Eligible orders: Completed status, appointment date set, no calendar event yet.',
            ],
        ],
        'ms' => [
            'summary' => 'Butang sync semua temujanji untuk backfill Google Calendar.',
            'changes' => [
                'Tambah Sync semua temujanji pada tetapan Google Calendar dengan progress berperingkat.',
                'Tambah arahan google-calendar:backfill untuk backfill di server.',
                'Pesanan layak: status Selesai, ada tarikh temujanji, belum ada acara kalendar.',
            ],
        ],
    ],
    '4.9.23' => [
        'en' => [
            'summary' => 'Modern Google Calendar settings UI with connection test.',
            'changes' => [
                'Redesign Google Calendar settings tab to match the Google Excel layout.',
                'Add Test connection for service account auth and calendar access.',
                'Show shared-credentials status card with link back to Google Excel settings.',
            ],
        ],
        'ms' => [
            'summary' => 'UI Tetapan Google Calendar moden dengan ujian sambungan.',
            'changes' => [
                'Reka semula tab tetapan Google Calendar mengikut susun atur Google Excel.',
                'Tambah Uji sambungan untuk pengesahan akaun perkhidmatan dan akses kalendar.',
                'Paparkan kad status kelayakan dikongsi dengan pautan ke tetapan Google Excel.',
            ],
        ],
    ],
    '4.9.22' => [
        'en' => [
            'summary' => 'Fix broken confirm dialogs on Settings System and related admin pages.',
            'changes' => [
                'Use @js() instead of @json() in HTML onclick/onsubmit attributes so confirm() strings no longer break JavaScript.',
                'Fixes console SyntaxError on Settings → System and restores Migrate, deploy, and catalog sync confirmations.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki dialog confirm rosak pada Tetapan Sistem dan halaman admin berkaitan.',
            'changes' => [
                'Guna @js() dan bukan @json() dalam atribut HTML onclick/onsubmit supaya string confirm() tidak rosak JavaScript.',
                'Baiki SyntaxError konsol pada Tetapan → Sistem dan pulihkan confirm Migrate, deploy, dan sync katalog.',
            ],
        ],
    ],
    '4.9.21' => [
        'en' => [
            'summary' => 'Google Sheets production hardening, admin fixes, and maintenance mode improvements.',
            'changes' => [
                'Isolate Google Calendar failures so a successful Sheets sync is not marked failed.',
                'Preserve service account JSON when the credentials field is left blank on save.',
                'Google Calendar stays off by default until explicitly enabled in settings.',
                'Queue Google sync on order update only when sheet-relevant fields change.',
                'Fix column picker checkboxes and allow countries/states API during maintenance mode.',
            ],
        ],
        'ms' => [
            'summary' => 'Pengukuhan production Google Sheets, pembaikan admin, dan mod penyelenggaraan.',
            'changes' => [
                'Pisahkan kegagalan Google Calendar supaya sync Sheets yang berjaya tidak ditanda gagal.',
                'Kekalkan JSON akaun perkhidmatan apabila medan kelayakan dibiarkan kosong semasa simpan.',
                'Google Calendar kekal dimatikan secara lalai sehingga diaktifkan dalam tetapan.',
                'Queue sync Google pada kemas kini pesanan hanya apabila medan berkaitan sheet berubah.',
                'Baiki checkbox pemilih kolum dan benarkan API countries/states semasa mod penyelenggaraan.',
            ],
        ],
    ],
    '4.9.20' => [
        'en' => [
            'summary' => 'Modern Google Excel settings UI and production queue deploy helpers.',
            'changes' => [
                'Redesign Google Excel settings with section cards, stats strip, and clearer sync workflow.',
                'Polish status tabs, column picker, alerts, sync log, and per-status column accordion.',
                'Add Supervisor and cron examples plus queue checks in verify-production-deploy.php.',
            ],
        ],
        'ms' => [
            'summary' => 'UI Tetapan Google Excel moden dan helper deploy queue production.',
            'changes' => [
                'Reka semula tetapan Google Excel dengan kad seksyen, jalur statistik, dan aliran sync yang lebih jelas.',
                'Perbaiki tab status, pemilih kolum, amaran, log sync, dan akordion kolum mengikut status.',
                'Tambah contoh Supervisor dan cron serta semakan queue dalam verify-production-deploy.php.',
            ],
        ],
    ],
    '4.9.19' => [
        'en' => [
            'summary' => 'Google Sheets sync polish: alerts, export, dashboard, per-status columns.',
            'changes' => [
                'Export sync activity log to CSV from Google Excel settings.',
                'Dashboard card shows failed Google Sheets sync count with link to affected orders.',
                'Optional email and WhatsApp alerts when order sync fails (6-hour throttle per order).',
                'Optional per-status column profiles override the global column set.',
            ],
        ],
        'ms' => [
            'summary' => 'Penambahbaikan sync Google Sheets: amaran, eksport, dashboard, kolum mengikut status.',
            'changes' => [
                'Eksport log aktiviti sync ke CSV dari tetapan Google Excel.',
                'Kad dashboard memaparkan bilangan sync Google Sheets gagal dengan pautan ke pesanan terlibat.',
                'Amaran e-mel dan WhatsApp pilihan apabila sync pesanan gagal (had 6 jam setiap pesanan).',
                'Profil kolum mengikut status pilihan mengatasi set kolum global.',
            ],
        ],
    ],
    '4.9.18' => [
        'en' => [
            'summary' => 'Customizable Google Sheets columns with JSON validation on save.',
            'changes' => [
                'Choose which order fields sync to Google Sheets and reorder columns in Settings.',
                'Add optional Spa Branch column when the Spa Branch module is enabled.',
                'Validate service account JSON when saving Google Excel settings.',
            ],
        ],
        'ms' => [
            'summary' => 'Kolum Google Sheets boleh suai dengan pengesahan JSON semasa simpan.',
            'changes' => [
                'Pilih medan pesanan yang disync ke Google Sheets dan susun semula kolum dalam Tetapan.',
                'Tambah kolum Cawangan Spa pilihan apabila modul Spa Branch diaktifkan.',
                'Sahkan JSON akaun perkhidmatan semasa menyimpan tetapan Google Excel.',
            ],
        ],
    ],
    '4.9.17' => [
        'en' => [
            'summary' => 'Background Google Sheets sync with activity log and bulk progress.',
            'changes' => [
                'Queue automatic sync on order create, update, and status change.',
                'Add recent sync activity log on the Google Excel settings tab.',
                'Bulk sync now runs in chunks with a progress bar.',
                'Filter orders list by Google Sheets sync failures.',
            ],
        ],
        'ms' => [
            'summary' => 'Sync Google Sheets latar belakang dengan log aktiviti dan progress pukal.',
            'changes' => [
                'Queue sync automatik bila pesanan dicipta, dikemas kini, atau status berubah.',
                'Tambah log aktiviti sync terkini pada tab Google Excel.',
                'Sync pukal kini berjalan dalam chunk dengan bar progress.',
                'Tapis senarai pesanan mengikut kegagalan sync Google Sheets.',
            ],
        ],
    ],
    '4.9.16' => [
        'en' => [
            'summary' => 'More reliable Google Sheets sync with edit, cleanup, and error visibility.',
            'changes' => [
                'Re-sync sheet rows when orders are created or edited in admin.',
                'Remove sheet rows when order status moves out of enabled sync statuses.',
                'Store and display last Google Sheets sync error on the order page.',
                'Improve retry-failed command and run it hourly via scheduler.',
            ],
        ],
        'ms' => [
            'summary' => 'Penyelarasan Google Sheets lebih boleh dipercayai dengan edit, bersihkan, dan ralat kelihatan.',
            'changes' => [
                'Sync semula baris helaian bila pesanan dicipta atau dikemas kini dalam admin.',
                'Buang baris helaian bila status pesanan keluar dari status sync yang diaktifkan.',
                'Simpan dan paparkan ralat sync Google Sheets terakhir pada halaman pesanan.',
                'Baiki arahan retry-failed dan jalankan setiap jam melalui scheduler.',
            ],
        ],
    ],
    '4.9.15' => [
        'en' => [
            'summary' => 'Google Sheets sync per order status with bulk backfill.',
            'changes' => [
                'Configure a sheet tab per order status in Settings → Google Excel.',
                'Rows move between tabs automatically when order status changes.',
                'Add Sync all orders now to backfill every enabled status.',
                'Order detail shows target tab and manual re-sync for enabled statuses.',
            ],
        ],
        'ms' => [
            'summary' => 'Sync Google Sheets mengikut status pesanan dengan backfill pukal.',
            'changes' => [
                'Konfigurasi tab helaian setiap status pesanan dalam Tetapan → Google Excel.',
                'Baris dipindahkan antara tab secara automatik apabila status pesanan berubah.',
                'Tambah Sync semua pesanan sekarang untuk backfill setiap status yang diaktifkan.',
                'Butiran pesanan paparkan tab sasaran dan sync semula manual untuk status yang diaktifkan.',
            ],
        ],
    ],
    '4.9.14' => [
        'en' => [
            'summary' => 'Add Google Sheets Service Account setup guide in admin settings.',
            'changes' => [
                'Collapsible step-by-step JSON key guide on the Google Excel settings tab.',
                'English and Bahasa Malaysia instructions with direct Google Cloud links.',
            ],
        ],
        'ms' => [
            'summary' => 'Tambah panduan setup Service Account Google Sheets dalam tetapan admin.',
            'changes' => [
                'Panduan langkah demi langkah kunci JSON boleh lipat pada tab Google Excel.',
                'Arahan BM dan Inggeris dengan pautan terus ke Google Cloud.',
            ],
        ],
    ],
    '4.9.13' => [
        'en' => [
            'summary' => 'Reliable Google Sheets sync for completed orders.',
            'changes' => [
                'Fix beautician Kanban completion to trigger Google Sheets sync.',
                'Add Test connection on Google Excel settings tab.',
                'Show sync status and manual Sync button on completed orders.',
                'Add google-sheets:backfill and google-sheets:retry-failed artisan commands.',
            ],
        ],
        'ms' => [
            'summary' => 'Penyelarasan Google Sheets yang lebih boleh dipercayai untuk pesanan selesai.',
            'changes' => [
                'Baiki penyiapan Kanban beautician supaya mencetuskan penyelarasan Google Sheets.',
                'Tambah Uji sambungan pada tab Google Excel.',
                'Paparkan status sync dan butang Sync manual pada pesanan selesai.',
                'Tambah arahan artisan google-sheets:backfill dan google-sheets:retry-failed.',
            ],
        ],
    ],
    '4.9.12' => [
        'en' => [
            'summary' => 'Show maintenance custom effect toggles directly under the Custom preset.',
            'changes' => [
                'Move per-effect checkboxes inline below Effect style when Custom is selected.',
                'Hide effect toggles for presets; show a preset info note instead.',
                'Move accent color above effect toggles; gradient help reflects store or custom color.',
                'Fix maintenance accent color validation (regex pipe delimiter).',
            ],
        ],
        'ms' => [
            'summary' => 'Paparkan togol kesan Custom terus di bawah preset Penyelenggaraan.',
            'changes' => [
                'Pindahkan kotak semak kesan ke bawah Gaya kesan apabila Custom dipilih.',
                'Sembunyikan togol kesan untuk preset; paparkan nota preset.',
                'Pindahkan warna aksen ke atas togol kesan; bantuan kecerunan ikut warna kedai atau tersuai.',
                'Baiki pengesahan warna aksen penyelenggaraan (pemisah regex pipe).',
            ],
        ],
    ],
    '4.9.11' => [
        'en' => [
            'summary' => 'Admin can customize maintenance page colors and background effects.',
            'changes' => [
                'Add Maintenance settings: effect presets (Aesthetic, Minimal, Classic, Custom), color source, and custom accent color picker.',
                'Add live preview and per-effect toggles for gradient, bokeh, shimmer, grain, and frosted card.',
                'Re-render the 503 maintenance page automatically when appearance settings are saved.',
            ],
        ],
        'ms' => [
            'summary' => 'Admin boleh sesuaikan warna dan kesan latar halaman penyelenggaraan.',
            'changes' => [
                'Tambah tetapan Penyelenggaraan: preset kesan (Aesthetic, Minimal, Classic, Custom), sumber warna, dan pemilih warna aksen.',
                'Tambah pratonton langsung dan togol kesan untuk kecerunan, bokeh, shimmer, grain, dan kad frosted.',
                'Render semula halaman 503 penyelenggaraan secara automatik apabila tetapan penampilan disimpan.',
            ],
        ],
    ],
    '4.9.10' => [
        'en' => [
            'summary' => 'Auto-refresh branded maintenance page after deploy on live servers.',
            'changes' => [
                'Fix production still showing plain Laravel 503 after GitHub update (old storage/framework/down file kept template: null).',
                'Refresh branded maintenance HTML automatically after deploy and on the next admin visit.',
                'Re-sync admin except paths for subdirectory installs such as /v2.',
            ],
        ],
        'ms' => [
            'summary' => 'Muat semula automatik halaman penyelenggaraan berjenama selepas deploy pada pelayan live.',
            'changes' => [
                'Baiki production masih papar 503 Laravel biasa selepas kemas kini GitHub (fail storage/framework/down lama kekal template: null).',
                'Muat semula HTML penyelenggaraan berjenama secara automatik selepas deploy dan pada lawatan admin seterusnya.',
                'Selaraskan semula laluan except admin untuk pemasangan subdirektori seperti /v2.',
            ],
        ],
    ],
    '4.9.9' => [
        'en' => [
            'summary' => 'Maintenance mode now blocks the storefront with a branded animated page.',
            'changes' => [
                'Fix maintenance toggle so storefront visitors see 503 while admin stays accessible.',
                'Remove admin bypass on the public site; correct admin except paths for subdirectory installs.',
                'Add branded maintenance page with store logo, EN/BM copy, and pink bokeh shimmer background.',
                'Sync maintenance checkbox with live artisan down/up state via MaintenanceModeService.',
            ],
        ],
        'ms' => [
            'summary' => 'Mod penyelenggaraan kini sekatan storefront dengan halaman animasi berjenama.',
            'changes' => [
                'Baiki togol penyelenggaraan supaya pelawat storefront nampak 503 manakala admin kekal boleh diakses.',
                'Buang pintasan admin pada laman awam; betulkan laluan except admin untuk pemasangan subdirektori.',
                'Tambah halaman penyelenggaraan berjenama dengan logo kedai, salinan EN/BM, dan latar bokeh merah jambu.',
                'Selaraskan kotak semak penyelenggaraan dengan keadaan artisan down/up melalui MaintenanceModeService.',
            ],
        ],
    ],
    '4.9.8' => [
        'en' => [
            'summary' => 'WhatsApp product share now uses the product image in link previews.',
            'changes' => [
                'Stop LayoutComposer from overwriting product/blog Open Graph tags with the store logo.',
                'Product pages now expose og:image from the product main image (or SEO OG image when set).',
            ],
        ],
        'ms' => [
            'summary' => 'Kongsi produk WhatsApp kini guna imej produk dalam pratonton pautan.',
            'changes' => [
                'Hentikan LayoutComposer menulis ganti tag Open Graph produk/blog dengan logo kedai.',
                'Halaman produk kini papar og:image daripada imej utama produk (atau imej OG SEO jika ditetapkan).',
            ],
        ],
    ],
    '4.9.7' => [
        'en' => [
            'summary' => 'Featured category slider shows 4 cards plus peek on desktop.',
            'changes' => [
                'Set Shop by Category desktop slidesPerView to 4.3 (four full cards and a peek of the fifth).',
            ],
        ],
        'ms' => [
            'summary' => 'Slider kategori pilihan papar 4 kad dan sedikit kad ke-5 pada desktop.',
            'changes' => [
                'Set slidesPerView desktop Shop by Category kepada 4.3 (empat kad penuh dan intipan kad kelima).',
            ],
        ],
    ],
    '4.9.6' => [
        'en' => [
            'summary' => 'Send gift page redesign and fix false WhatsApp success on local.',
            'changes' => [
                'Redesign /send-gift with voucher preview, romantic layout, and live name overlay.',
                'Save generated vouchers to media/gift-vouchers with clearer image error messages.',
                'Fail gift send when OneSender skips (no more false “sent” on local/dev).',
                'Use absolute image URLs and immediate delivery for gift voucher WhatsApp.',
            ],
        ],
        'ms' => [
            'summary' => 'Reka semula halaman hantar hadiah dan baiki kejayaan WhatsApp palsu pada local.',
            'changes' => [
                'Reka semula /send-gift dengan pratonton baucar, susun atur romantis, dan overlay nama langsung.',
                'Simpan baucar dijana ke media/gift-vouchers dengan mesej ralat imej yang lebih jelas.',
                'Gagalkan penghantaran hadiah apabila OneSender di-skip (tiada lagi “dihantar” palsu pada local/dev).',
                'Guna URL imej mutlak dan penghantaran segera untuk WhatsApp baucar hadiah.',
            ],
        ],
    ],
    '4.9.5' => [
        'en' => [
            'summary' => 'Fix /send-gift page missing notification partial.',
            'changes' => [
                'Use the correct storefront auth notification partial on the send gift page.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki halaman /send-gift: partial notifikasi tidak dijumpai.',
            'changes' => [
                'Guna partial notifikasi auth storefront yang betul pada halaman hantar hadiah.',
            ],
        ],
    ],
    '4.9.4' => [
        'en' => [
            'summary' => 'Special gift: default voucher design and WhatsApp message template.',
            'changes' => [
                'Bundle a default gift voucher background; custom upload or media library still optional.',
                'Pre-fill WhatsApp caption template with sensible placeholders when empty.',
                'Center recipient name and order number on generated vouchers.',
                'Image picker supports default preview with reset-to-default on remove.',
            ],
        ],
        'ms' => [
            'summary' => 'Hadiah istimewa: reka bentuk baucar lalai dan templat mesej WhatsApp.',
            'changes' => [
                'Sertakan latar baucar hadiah lalai; muat naik atau pustaka media masih pilihan.',
                'Isi templat kapsyen WhatsApp dengan placeholder sesuai apabila kosong.',
                'Pusatkan nama penerima dan nombor pesanan pada baucar dijana.',
                'Pemilih imej menyokong preview lalai dengan reset ke lalai selepas buang.',
            ],
        ],
    ],
    '4.9.3' => [
        'en' => [
            'summary' => 'Fix CHIP public key fetch from GET /public_key/.',
            'changes' => [
                'Parse JSON-encoded PEM string returned by CHIP public_key endpoint.',
                'Webhook verifier auto-fetch now works when chip_public_key is empty.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki fetch kunci awam CHIP dari GET /public_key/.',
            'changes' => [
                'Parse rentetan PEM berkod JSON yang dikembalikan oleh endpoint public_key CHIP.',
                'Auto-fetch pengesah webhook kini berfungsi apabila chip_public_key kosong.',
            ],
        ],
    ],
    '4.9.2' => [
        'en' => [
            'summary' => 'Harden CHIP Collect: RSA webhook verification and Atome whitelist fix.',
            'changes' => [
                'Verify CHIP X-Signature with RSA public key (SHA-256) instead of shared secret.',
                'Add chip_public_key admin setting; auto-fetch from GET /public_key/ when empty.',
                'Return HTTP 200 on invalid webhook signatures (CHIP retry-safe).',
                'Default Atome whitelist to razer_atome with API auto-resolve.',
                'Fix chip_atome checkout validation and explicit payment gateway IDs.',
            ],
        ],
        'ms' => [
            'summary' => 'Perkukuh CHIP Collect: pengesahan webhook RSA dan baiki whitelist Atome.',
            'changes' => [
                'Sahkan X-Signature CHIP dengan kunci awam RSA (SHA-256) dan bukan rahsia kongsi.',
                'Tambah tetapan admin chip_public_key; auto-fetch dari GET /public_key/ jika kosong.',
                'Pulangkan HTTP 200 pada tandatangan webhook tidak sah (selamat untuk retry CHIP).',
                'Lalai whitelist Atome ke razer_atome dengan auto-resolve API.',
                'Baiki validasi checkout chip_atome dan ID gateway pembayaran eksplisit.',
            ],
        ],
    ],
    '4.9.1' => [
        'en' => [
            'summary' => 'Fix generic CHIP gateway showing when all-methods checkout is disabled.',
            'changes' => [
                'Respect the “Show generic CHIP — all methods” admin setting at checkout.',
                'Only register the umbrella chip gateway when enabled or no per-method options are active.',
                'Keep legacy chip orders completable via PaymentGatewayResolver.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki gateway CHIP generik yang masih dipaparkan walaupun semua kaedah dimatikan.',
            'changes' => [
                'Hormati tetapan admin “Tunjuk checkout generik CHIP — semua kaedah” pada checkout.',
                'Daftar gateway chip umbrella hanya apabila diaktifkan atau tiada pilihan per-kaedah aktif.',
                'Kekalkan pesanan chip lama boleh diselesaikan melalui PaymentGatewayResolver.',
            ],
        ],
    ],
    '4.9.0' => [
        'en' => [
            'summary' => 'Sync cart and checkout pricing with savings breakdown.',
            'changes' => [
                'Fix sidebar cart product images using assetUrl instead of locale-prefixed static URLs.',
                'Show strikethrough regular price and sale price on cart drawer, cart page, and checkout.',
                'Add green savings badge per discounted line (Save amount and percent off).',
                'Cart summary: item lines with dual prices plus Regular price, Subtotal, and You save rows.',
                'Checkout order summary matches cart pricing layout; fix Alpine rendering for summary rows.',
                'Eager-load variant image files when storing cart items.',
            ],
        ],
        'ms' => [
            'summary' => 'Selaraskan harga troli dan checkout dengan pecahan penjimatan.',
            'changes' => [
                'Baiki imej produk troli sisi menggunakan assetUrl dan bukan URL statik berawalan locale.',
                'Paparkan harga asal dicoret dan harga promo pada laci troli, halaman troli, dan checkout.',
                'Tambah lencana jimat hijau setiap baris diskaun (jumlah dan peratus jimat).',
                'Ringkasan troli: baris item dengan dua harga serta Harga asal, Subjumlah, dan Anda jimat.',
                'Ringkasan pesanan checkout selaras dengan susun atur harga troli; baiki paparan baris Alpine.',
                'Muat awal fail imej varian semasa menyimpan item troli.',
            ],
        ],
    ],
    '4.8.7' => [
        'en' => [
            'summary' => 'Center mobile product slider pagination dots between nav arrows.',
            'changes' => [
                'Use a CSS grid layout so swiper dots stay centered between prev and next on mobile sliders.',
                'Disable dynamicBullets in product slider pagination.',
            ],
        ],
        'ms' => [
            'summary' => 'Centerkan titik pagination slider produk antara anak panah nav pada mudah alih.',
            'changes' => [
                'Guna susun atur CSS grid supaya titik swiper kekal di tengah antara prev dan next pada slider mudah alih.',
                'Matikan dynamicBullets dalam pagination slider produk.',
            ],
        ],
    ],
    '4.8.6' => [
        'en' => [
            'summary' => 'Fix raw View All translation key on product tab headers.',
            'changes' => [
                'Resolve the storefront View All link label so it shows translated text instead of the lang key.',
            ],
        ],
        'ms' => [
            'summary' => 'Baiki kunci terjemahan View All mentah pada header tab produk.',
            'changes' => [
                'Selesaikan label pautan Lihat Semua supaya papar teks terjemahan dan bukan kunci lang.',
            ],
        ],
    ],
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
