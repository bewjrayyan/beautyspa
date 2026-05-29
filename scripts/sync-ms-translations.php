<?php

/**
 * Sync module Resources/lang/ms PHP files from en counterparts.
 * Keeps existing ms strings that differ from en (already translated).
 * Fills missing or English-copy values using the Malay glossary below.
 *
 * Usage: php scripts/sync-ms-translations.php [--dry-run] [--force] [--reformat]
 */

declare(strict_types=1);

$dryRun = in_array('--dry-run', $argv ?? [], true);
$force = in_array('--force', $argv ?? [], true);
$reformat = in_array('--reformat', $argv ?? [], true);
$root = dirname(__DIR__);

require $root . '/vendor/autoload.php';

final class MalayGlossary
{
    /** @var array<string, string> Longest phrases first when applied via strtr after sorting keys by length. */
    public static function phrases(): array
    {
        return [
            'Are you sure you want to delete?' => 'Adakah anda pasti mahu memadam?',
            'Are you sure you want to delete this?' => 'Adakah anda pasti mahu memadam ini?',
            'No matching records found' => 'Tiada rekod sepadan',
            'No data available in table' => 'Tiada data dalam jadual',
            'No data available!' => 'Tiada data tersedia!',
            'Search here...' => 'Cari di sini...',
            'Please Select' => 'Sila pilih',
            'Please select' => 'Sila pilih',
            'Save & Edit' => 'Simpan & Sunting',
            'Save & Exit' => 'Simpan & Keluar',
            'Save and Edit' => 'Simpan & Sunting',
            'Save and Exit' => 'Simpan & Keluar',
            'Keyboard shortcuts' => 'Pintasan papan kekunci',
            'Available keyboard shortcuts on this page' => 'Pintasan papan kekunci tersedia pada halaman ini',
            'Back to :name Index' => 'Kembali ke Indeks :name',
            'Showing _START_ to _END_ of _TOTAL_ entries' => 'Memaparkan _START_ hingga _END_ daripada _TOTAL_ entri',
            '(filtered from _MAX_ total entries' => '(ditapis daripada _MAX_ jumlah entri)',
            '(filtered from _MAX_ total entries)' => '(ditapis daripada _MAX_ jumlah entri)',
            'Showing 0 to 0 of 0 entries' => 'Memaparkan 0 hingga 0 daripada 0 entri',
            'Show _MENU_ entries' => 'Papar _MENU_ entri',
            'Drop files here or click to upload' => 'Lepaskan fail di sini atau klik untuk muat naik',
            'Upload New File' => 'Muat Naik Fail Baharu',
            'Open File' => 'Buka Fail',
            'Download Sample File' => 'Muat Turun Fail Contoh',
            'Product Data (CSV or Excel)' => 'Data Produk (CSV atau Excel)',
            'Product Images (ZIP)' => 'Imej Produk (ZIP)',
            'Products imported successfully!' => 'Produk berjaya diimport!',
            'Write to Database Failed.' => 'Gagal menulis ke pangkalan data.',
            'Product creation failed.' => 'Gagal mencipta produk.',
            'Import Products' => 'Import Produk',
            'Flash Sale Information' => 'Maklumat Jualan Kilat',
            'Coupon Information' => 'Maklumat Kupon',
            'Campaign Name' => 'Nama Kempen',
            'Attribute Sets' => 'Set Atribut',
            'All Products' => 'Semua Produk',
            'Create Product' => 'Cipta Produk',
            'Featured Image' => 'Imej Utama',
            'Publish Status' => 'Status Penerbitan',
            'Payment Status' => 'Status Bayaran',
            'Payment Method' => 'Kaedah Bayaran',
            'Order Tracking' => 'Penjejakan Pesanan',
            'Tracking Reference' => 'Rujukan Penjejakan',
            'Order & Account Information' => 'Maklumat Pesanan & Akaun',
            'Order Information' => 'Maklumat Pesanan',
            'Order Date' => 'Tarikh Pesanan',
            'Order Status' => 'Status Pesanan',
            'Order ID' => 'ID Pesanan',
            'Customer Name' => 'Nama Pelanggan',
            'Customer Email' => 'E-mel Pelanggan',
            'Currency Rate' => 'Kadar Mata Wang',
            'Send Email' => 'Hantar E-mel',
            'View order' => 'Lihat pesanan',
            'Print invoice' => 'Cetak invois',
            'Change status' => 'Tukar status',
            'Store performance at a glance' => 'Prestasi kedai secara ringkas',
            "Today's Appointments" => 'Temujanji Hari Ini',
            'Treatment Sales' => 'Jualan Rawatan',
            'Total Sales' => 'Jumlah Jualan',
            'Total Orders' => 'Jumlah Pesanan',
            'Total Products' => 'Jumlah Produk',
            'Total Customers' => 'Jumlah Pelanggan',
            'Latest Searches' => 'Carian Terkini',
            'Latest Orders' => 'Pesanan Terkini',
            'Latest Reviews' => 'Ulasan Terkini',
            'Sales Analytics' => 'Analitik Jualan',
            'Beautician Analytics' => 'Analitik Beautician',
            'Flash Sales' => 'Jualan Kilat',
            'Flash Sale' => 'Jualan Kilat',
            'Blog Post' => 'Catatan Blog',
            'Blog Posts' => 'Catatan Blog',
            'Generate Sitemap' => 'Jana peta laman',
            'Sitemap generated successfully' => 'Peta laman berjaya dijana',
            'Loyalty & Membership' => 'Kesetiaan & Keahlian',
            'Lifetime spend' => 'Perbelanjaan terkumpul',
            'Lifetime spend (RM)' => 'Perbelanjaan terkumpul (RM)',
            'Minimum lifetime spend (RM)' => 'Minimum perbelanjaan terkumpul (RM)',
            'Min. lifetime spend (RM)' => 'Min. perbelanjaan terkumpul (RM)',
            'First Name' => 'Nama pertama',
            'Last Name' => 'Nama keluarga',
            'Add to Cart' => 'Tambah ke Troli',
            'Your cart is empty' => 'Troli anda kosong.',
            'Checkout' => 'Bayar',
            'Order Complete' => 'Pesanan Selesai',
            'The page you are looking for was not found' => 'Halaman yang anda cari tidak wujud',
            'Oops! This page was not found' => 'Halaman tidak dijumpai',
            'Oops! Something went wrong' => 'Sesuatu telah berlaku',
            'An administrator was notified' => 'Pentadbir telah dimaklumkan',
            'Lifetime revenue from all orders' => 'Jumlah hasil daripada semua pesanan',
            'Orders excluding canceled' => 'Pesanan tidak termasuk dibatalkan',
            'Revenue from beautician treatment bookings' => 'Hasil tempahan rawatan beautician',
            'Appointments scheduled for today' => 'Temujanji dijadualkan hari ini',
            'Products in your catalog' => 'Produk dalam katalog',
            'Registered customer accounts' => 'Akaun pelanggan berdaftar',
            'Monday' => 'Isnin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Khamis',
            'Friday' => 'Jumaat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Ahad',
            'This field is required' => 'Medan ini diperlukan',
            'This is the first name' => 'Ini ialah nama pertama',
            'Postcode / ZIP' => 'Poskod / ZIP',
            'Your profile has been updated' => 'Profil anda telah dikemas kini',
            'The default address has been updated' => 'Alamat lalai telah dikemas kini',
            'The address has been created' => 'Alamat telah dicipta',
            'The address has been updated' => 'Alamat telah dikemas kini',
            'The address has been deleted' => 'Alamat telah dipadam',
            ':resource created' => ':resource telah dicipta',
            ':resource updated' => ':resource telah dikemas kini',
            ':resource deleted' => ':resource telah dipadam',
            'Use this attribute for filtering products' => 'Gunakan atribut ini untuk menapis produk',
            'Leave empty to auto-create a portal login, or link an existing admin user.' => 'Biarkan kosong untuk cipta log masuk portal automatik, atau pautkan pengguna admin sedia ada.',
            'Preview job sheet portal' => 'Pratonton portal job sheet',
            'log in as the linked user' => 'log masuk sebagai pengguna yang dipautkan',
            'Job sheet' => 'Job sheet',
            'Front Desk / Receptionist' => 'Kaunter Depan / Penyambut tetamu',
            'Treatment orders marked completed' => 'Pesanan rawatan ditanda selesai',
            'Upcoming Bookings' => 'Tempahan Akan Datang',
            'Treatment Items' => 'Item Rawatan',
            'With Appointment' => 'Dengan Temujanji',
            'Completed in filtered set' => 'Selesai dalam set ditapis',
            'Sum of treatment line quantities' => 'Jumlah kuantiti baris rawatan',
            'Detailed treatment orders with customer, beautician, and appointment info.' => 'Pesanan rawatan terperinci dengan maklumat pelanggan, beautician, dan temujanji.',
            'records on this page' => 'rekod pada halaman ini',
            'Booked At' => 'Ditempah Pada',
            'Enable the blog category' => 'Aktifkan kategori blog',
            'Enable the brand' => 'Aktifkan jenama',
            'Sorry, the product is out of stock' => 'Maaf, produk ini kehabisan stok',
            'Sorry, we only have :stock in stock' => 'Maaf, kami hanya ada :stock dalam stok',
            'Sorry, one or more product is out of stock' => 'Maaf, satu atau lebih produk kehabisan stok',
            "Sorry, one or more product doesn't have enough stock" => 'Maaf, satu atau lebih produk tidak mempunyai stok mencukupi',
            'Portal account' => 'Akaun portal',
            'First Name' => 'Nama Pertama',
            'Last Name' => 'Nama Akhir',
            'Full Name' => 'Nama Penuh',
            'Company Name' => 'Nama Syarikat',
            'Street Address' => 'Alamat Jalan',
            'Postcode' => 'Poskod',
            'State / Province' => 'Negeri / Wilayah',
            'Shipping Address' => 'Alamat Penghantaran',
            'Billing Address' => 'Alamat Bil',
            'Order Summary' => 'Ringkasan Pesanan',
            'Place Order' => 'Buat Pesanan',
            'Continue Shopping' => 'Teruskan Membeli-belah',
            'Proceed to Checkout' => 'Teruskan ke Daftar Keluar',
            'Apply Coupon' => 'Guna Kupon',
            'Remove Coupon' => 'Buang Kupon',
            'Have a coupon?' => 'Ada kupon?',
            'Enter your coupon code' => 'Masukkan kod kupon anda',
            'Shipping method' => 'Kaedah penghantaran',
            'Payment method' => 'Kaedah bayaran',
            'Order placed successfully' => 'Pesanan berjaya dibuat',
            'Thank you for your order' => 'Terima kasih atas pesanan anda',
            'View Order' => 'Lihat Pesanan',
            'Track Order' => 'Jejak Pesanan',
            'My Orders' => 'Pesanan Saya',
            'My Account' => 'Akaun Saya',
            'My Profile' => 'Profil Saya',
            'My Wishlist' => 'Senarai Hajat Saya',
            'My Addresses' => 'Alamat Saya',
            'My Downloads' => 'Muat Turun Saya',
            'My Reviews' => 'Ulasan Saya',
            'Change Password' => 'Tukar Kata Laluan',
            'Current Password' => 'Kata Laluan Semasa',
            'New Password' => 'Kata Laluan Baharu',
            'Confirm Password' => 'Sahkan Kata Laluan',
            'Remember Me' => 'Ingat Saya',
            'Forgot Password?' => 'Lupa Kata Laluan?',
            'Reset Password' => 'Set Semula Kata Laluan',
            'Create an account' => 'Cipta akaun',
            'Already have an account?' => 'Sudah mempunyai akaun?',
            "Don't have an account?" => 'Belum mempunyai akaun?',
            'Sign in with' => 'Log masuk dengan',
            'Or continue with' => 'Atau teruskan dengan',
            'Out of stock' => 'Kehabisan stok',
            'In stock' => 'Dalam stok',
            'Add to Cart' => 'Tambah ke Troli',
            'Add to Wishlist' => 'Tambah ke Senarai Hajat',
            'Buy Now' => 'Beli Sekarang',
            'Quick View' => 'Lihat Pantas',
            'Related Products' => 'Produk Berkaitan',
            'Product Details' => 'Butiran Produk',
            'Additional Information' => 'Maklumat Tambahan',
            'Customer Reviews' => 'Ulasan Pelanggan',
            'Write a review' => 'Tulis ulasan',
            'No reviews yet' => 'Tiada ulasan lagi',
            'Clear Cache' => 'Kosongkan Cache',
            'Cache cleared successfully' => 'Cache berjaya dikosongkan',
            'Settings saved' => 'Tetapan disimpan',
            'Settings updated' => 'Tetapan dikemas kini',
            'Successfully created' => 'Berjaya dicipta',
            'Successfully updated' => 'Berjaya dikemas kini',
            'Successfully deleted' => 'Berjaya dipadam',
            'Something went wrong' => 'Sesuatu telah berlaku',
            'Invalid credentials' => 'Kelayakan tidak sah',
            'Account created' => 'Akaun dicipta',
            'Check your email' => 'Semak e-mel anda',
            'Password has been reset' => 'Kata laluan telah diset semula',
            'Account not activated' => 'Akaun tidak diaktifkan',
            'Account is blocked' => 'Akaun disekat',
            'Expand All' => 'Kembangkan Semua',
            'Collapse All' => 'Runtuhkan Semua',
            "Don't Track Inventory" => 'Jangan Jejak Inventori',
            'Track Inventory' => 'Jejak Inventori',
            'Inventory Management' => 'Pengurusan Inventori',
            'Choose an option' => 'Pilih satu pilihan',
            'Open in new window' => 'Buka dalam tetingkap baharu',
            'Theme Color' => 'Warna Tema',
            'Merchant ID' => 'ID Pedagang',
            'WhatsApp OTP' => 'OTP WhatsApp',
            'Custom Checkbox' => 'Kotak Semak Tersuai',
            'Custom Radio Button' => 'Butang Radio Tersuai',
            'Radio Button' => 'Buton Radio',
            'Textarea' => 'Kawasan teks',
            'Dropdown' => 'Menu lungsur',
            'Checkbox' => 'Kotak semak',
            'Variants' => 'Varian',
            'Variant' => 'Varian',
            'Newsletter' => 'Surat berita',
            'Comment' => 'Komen',
            'Filename' => 'Nama fail',
            'Availability' => 'Ketersediaan',
            'Direction' => 'Arah',
            'Sandbox' => 'Sandbox',
            'Caption 1' => 'Kapsyen 1',
            'Caption 2' => 'Kapsyen 2',
            'Blog Tag' => 'Tag Blog',
            'Job Sheet' => 'Job Sheet',
            'Available' => 'Tersedia',
            'Linked Products' => 'Produk Berkaitan',
            'SEO' => 'SEO',
            'Pricing' => 'Penetapan Harga',
            'Inventory' => 'Inventori',
            'Downloads' => 'Muat turun',
            'Additional' => 'Tambahan',
            'Values' => 'Nilai',
            'Choose' => 'Pilih',
            'Insert' => 'Sisip',
            'Apply' => 'Guna',
            'Color' => 'Warna',
            'Text' => 'Teks',
            'Template' => 'Templat',
            'Clone' => 'Klon',
            'Virtual/Treatment' => 'Maya/Rawatan',
            'Order saved' => 'Susunan disimpan',
            'order saved' => 'susunan disimpan',
        ];
    }

    /** @return array<string, string> */
    public static function words(): array
    {
        return [
            'Dashboard' => 'Papan Pemuka',
            'Storefront' => 'Kedai',
            'Products' => 'Produk',
            'Product' => 'Produk',
            'Categories' => 'Kategori',
            'Category' => 'Kategori',
            'Orders' => 'Pesanan',
            'Order' => 'Pesanan',
            'Customers' => 'Pelanggan',
            'Customer' => 'Pelanggan',
            'Users' => 'Pengguna',
            'User' => 'Pengguna',
            'Roles' => 'Peranan',
            'Role' => 'Peranan',
            'Settings' => 'Tetapan',
            'Setting' => 'Tetapan',
            'Reports' => 'Laporan',
            'Report' => 'Laporan',
            'Reviews' => 'Ulasan',
            'Review' => 'Ulasan',
            'Coupons' => 'Kupon',
            'Coupon' => 'Kupon',
            'Taxes' => 'Cukai',
            'Tax' => 'Cukai',
            'Tags' => 'Tag',
            'Tag' => 'Tag',
            'Pages' => 'Halaman',
            'Page' => 'Halaman',
            'Menus' => 'Menu',
            'Menu' => 'Menu',
            'Media' => 'Media',
            'Import' => 'Import',
            'Export' => 'Eksport',
            'Translations' => 'Terjemahan',
            'Translation' => 'Terjemahan',
            'Languages' => 'Bahasa',
            'Language' => 'Bahasa',
            'Localization' => 'Penyetempatan',
            'Attributes' => 'Atribut',
            'Attribute' => 'Atribut',
            'Variations' => 'Variasi',
            'Variation' => 'Variasi',
            'Options' => 'Pilihan',
            'Option' => 'Pilihan',
            'Brands' => 'Jenama',
            'Brand' => 'Jenama',
            'Sliders' => 'Slider',
            'Slider' => 'Slider',
            'Blog' => 'Blog',
            'Posts' => 'Catatan',
            'Post' => 'Catatan',
            'Content' => 'Kandungan',
            'Sales' => 'Jualan',
            'System' => 'Sistem',
            'Appearance' => 'Penampilan',
            'Tools' => 'Alat',
            'Update' => 'Kemas Kini',
            'General' => 'Umum',
            'Save' => 'Simpan',
            'Cancel' => 'Batal',
            'Delete' => 'Padam',
            'Edit' => 'Sunting',
            'Create' => 'Cipta',
            'Add' => 'Tambah',
            'Remove' => 'Buang',
            'Search' => 'Cari',
            'Filter' => 'Tapis',
            'Actions' => 'Tindakan',
            'Action' => 'Tindakan',
            'Status' => 'Status',
            'Active' => 'Aktif',
            'Inactive' => 'Tidak aktif',
            'Approved' => 'Diluluskan',
            'Pending' => 'Menunggu',
            'Loading' => 'Memuatkan',
            'Previous' => 'Sebelum',
            'Next' => 'Seterusnya',
            'Confirmation' => 'Pengesahan',
            'Name' => 'Nama',
            'Title' => 'Tajuk',
            'Description' => 'Penerangan',
            'Email' => 'E-mel',
            'Phone' => 'Telefon',
            'Password' => 'Kata laluan',
            'Address' => 'Alamat',
            'City' => 'Bandar',
            'State' => 'Negeri',
            'Country' => 'Negara',
            'Zip' => 'Poskod',
            'Total' => 'Jumlah',
            'Subtotal' => 'Subjumlah',
            'Discount' => 'Diskaun',
            'Shipping' => 'Penghantaran',
            'Quantity' => 'Kuantiti',
            'Price' => 'Harga',
            'Amount' => 'Amaun',
            'Date' => 'Tarikh',
            'Time' => 'Masa',
            'Created' => 'Dicipta',
            'Updated' => 'Dikemas kini',
            'Yes' => 'Ya',
            'No' => 'Tidak',
            'Enabled' => 'Diaktifkan',
            'Disabled' => 'Dinyahaktifkan',
            'Default' => 'Lalai',
            'Image' => 'Imej',
            'Images' => 'Imej',
            'File' => 'Fail',
            'Files' => 'Fail',
            'Browse' => 'Layari',
            'Upload' => 'Muat naik',
            'Download' => 'Muat turun',
            'Print' => 'Cetak',
            'View' => 'Lihat',
            'Show' => 'Papar',
            'Hide' => 'Sembunyi',
            'Select' => 'Pilih',
            'Selected' => 'Dipilih',
            'All' => 'Semua',
            'None' => 'Tiada',
            'Keyword' => 'Kata kunci',
            'Results' => 'Keputusan',
            'Hits' => 'Lawatan',
            'Rating' => 'Penilaian',
            'Code' => 'Kod',
            'Permissions' => 'Kebenaran',
            'Permission' => 'Kebenaran',
            'Information' => 'Maklumat',
            'Details' => 'Butiran',
            'Summary' => 'Ringkasan',
            'Notes' => 'Nota',
            'Message' => 'Mesej',
            'Messages' => 'Mesej',
            'Notifications' => 'Pemberitahuan',
            'Notification' => 'Pemberitahuan',
            'Members' => 'Ahli',
            'Member' => 'Ahli',
            'Tiers' => 'Tier',
            'Tier' => 'Tier',
            'Reservations' => 'Tempahan',
            'Reservation' => 'Tempahan',
            'Beauticians' => 'Beautician',
            'Beautician' => 'Beautician',
            'Analytics' => 'Analitik',
            'Sitemap' => 'Peta laman',
            'Generate' => 'Jana',
            'Currency' => 'Mata wang',
            'Transactions' => 'Transaksi',
            'Transaction' => 'Transaksi',
            'Invoices' => 'Invois',
            'Invoice' => 'Invois',
            'Receipt' => 'Resit',
            'Checkout' => 'Daftar keluar',
            'Cart' => 'Troli',
            'Wishlist' => 'Senarai hajat',
            'Compare' => 'Banding',
            'Account' => 'Akaun',
            'Profile' => 'Profil',
            'Login' => 'Log masuk',
            'Logout' => 'Log keluar',
            'Register' => 'Daftar',
            'Sign in' => 'Log masuk',
            'Sign up' => 'Daftar',
            'Forgot' => 'Lupa',
            'Reset' => 'Set semula',
            'Submit' => 'Hantar',
            'Confirm' => 'Sahkan',
            'Close' => 'Tutup',
            'Back' => 'Kembali',
            'Continue' => 'Teruskan',
            'Success' => 'Berjaya',
            'Error' => 'Ralat',
            'Warning' => 'Amaran',
            'Failed' => 'Gagal',
            'Successful' => 'Berjaya',
            'Required' => 'Diperlukan',
            'Optional' => 'Pilihan',
            'Guest' => 'Tetamu',
            'Registered' => 'Berdaftar',
            'Filterable' => 'Boleh ditapis',
            'Published' => 'Diterbitkan',
            'Unpublished' => 'Tidak diterbitkan',
            'Completed' => 'Selesai',
            'Canceled' => 'Dibatalkan',
            'Cancelled' => 'Dibatalkan',
            'Processing' => 'Sedang diproses',
            'On Hold' => 'Ditahan',
            'Refunded' => 'Dibayar balik',
            'Shipped' => 'Dihantar',
            'Delivered' => 'Disampaikan',
            'Pending Payment' => 'Bayaran Menunggu',
            'Paid' => 'Dibayar',
            'Unpaid' => 'Belum dibayar',
            'Free' => 'Percuma',
            'Fixed' => 'Tetap',
            'Percent' => 'Peratus',
            'Percentage' => 'Peratusan',
            'Flat' => 'Rata',
            'Shipping' => 'Penghantaran',
            'Billing' => 'Bil',
            'Thumbnail' => 'Imej kecil',
            'Short Description' => 'Penerangan Ringkas',
            'Long Description' => 'Penerangan Panjang',
            'Meta Title' => 'Tajuk Meta',
            'Meta Description' => 'Penerangan Meta',
            'Slug' => 'Slug',
            'SKU' => 'SKU',
            'Stock' => 'Stok',
            'In Stock' => 'Dalam Stok',
            'Out of Stock' => 'Kehabisan Stok',
            'Manage Stock' => 'Urus Stok',
            'Special Price' => 'Harga Istimewa',
            'Regular Price' => 'Harga Biasa',
            'Sale Price' => 'Harga Jualan',
            'From' => 'Dari',
            'To' => 'Hingga',
            'Start' => 'Mula',
            'End' => 'Tamat',
            'Start Date' => 'Tarikh Mula',
            'End Date' => 'Tarikh Tamat',
            'Start Time' => 'Masa Mula',
            'End Time' => 'Masa Tamat',
            'Is Active' => 'Aktif',
            'Is Featured' => 'Pilihan Utama',
            'Featured' => 'Pilihan Utama',
            'New' => 'Baharu',
            'Old' => 'Lama',
            'Type' => 'Jenis',
            'Value' => 'Nilai',
            'Label' => 'Label',
            'Key' => 'Kunci',
            'Group' => 'Kumpulan',
            'Position' => 'Kedudukan',
            'Weight' => 'Berat',
            'Sort Order' => 'Susunan',
            'Ascending' => 'Menaik',
            'Descending' => 'Menurun',
            'Ascending order' => 'Susunan menaik',
            'Descending order' => 'Susunan menurun',
            'Expand' => 'Kembangkan',
            'Collapse' => 'Runtuhkan',
            'Linked' => 'Dipautkan',
            'Comment' => 'Komen',
            'Newsletter' => 'Surat berita',
            'Filename' => 'Nama fail',
            'Availability' => 'Ketersediaan',
            'Direction' => 'Arah',
            'Inventory' => 'Inventori',
            'Pricing' => 'Harga',
            'Additional' => 'Tambahan',
            'Values' => 'Nilai',
            'Choose' => 'Pilih',
            'Insert' => 'Sisip',
            'Apply' => 'Guna',
            'Color' => 'Warna',
            'Text' => 'Teks',
            'Template' => 'Templat',
            'Clone' => 'Klon',
            'Variant' => 'Varian',
            'Variants' => 'Varian',
            'Dropdown' => 'Menu lungsur',
            'Checkbox' => 'Kotak semak',
            'Textarea' => 'Kawasan teks',
            'Available' => 'Tersedia',
            'Caption' => 'Kapsyen',
        ];
    }
}

final class MsTranslationSync
{
    public function __construct(
        private readonly string $root,
        private readonly bool $dryRun,
        private readonly bool $force,
    ) {}

    public function run(): void
    {
        $enFiles = $this->findEnFiles();
        $updated = 0;
        $created = 0;

        foreach ($enFiles as $enPath) {
            $msPath = str_replace('/lang/en/', '/lang/ms/', $enPath);

            $en = $this->loadArray($enPath);
            $existingMs = is_file($msPath) ? $this->loadArray($msPath) : [];

            $merged = $this->merge($en, $existingMs, $this->force);
            $changed = $merged !== $existingMs || ! is_file($msPath);

            if (! $changed) {
                continue;
            }

            if (! is_file($msPath)) {
                $created++;
            } else {
                $updated++;
            }

            if ($this->dryRun) {
                echo "[dry-run] Would write: {$msPath}\n";
                continue;
            }

            $this->writeArray($msPath, $merged);
            echo "Written: {$msPath}\n";
        }

        echo "\nDone. Created: {$created}, Updated: {$updated}, Total en files: " . count($enFiles) . "\n";
    }

    public function reformatAll(): void
    {
        $count = 0;
        foreach ($this->findLangFiles('/lang/ms/') as $msPath) {
            if ($this->dryRun) {
                echo "[dry-run] Would reformat: {$msPath}\n";
                continue;
            }
            $this->writeArray($msPath, $this->loadArray($msPath));
            $count++;
        }
        echo "\nReformatted {$count} ms language files.\n";
    }

    /** @return list<string> */
    private function findEnFiles(): array
    {
        return $this->findLangFiles('/lang/en/');
    }

    /** @return list<string> */
    private function findLangFiles(string $segment): array
    {
        $files = [];
        foreach (['modules', 'resources'] as $base) {
            $dir = "{$this->root}/{$base}";
            if (! is_dir($dir)) {
                continue;
            }
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                $path = $file->getPathname();
                if (str_contains($path, $segment) && str_ends_with($path, '.php')) {
                    $files[] = $path;
                }
            }
        }

        sort($files);

        return $files;
    }

    /** @return array<string, mixed> */
    private function loadArray(string $path): array
    {
        $data = include $path;

        return is_array($data) ? $data : [];
    }

    /**
     * @param array<string, mixed> $en
     * @param array<string, mixed> $ms
     * @return array<string, mixed>
     */
    private function merge(array $en, array $ms, bool $force): array
    {
        $out = [];

        foreach ($en as $key => $enValue) {
            $msValue = $ms[$key] ?? null;

            if (is_array($enValue)) {
                $out[$key] = $this->merge(
                    $enValue,
                    is_array($msValue) ? $msValue : [],
                    $force
                );
                continue;
            }

            if (! is_string($enValue)) {
                $out[$key] = $msValue ?? $enValue;
                continue;
            }

            if (
                ! $force
                && is_string($msValue)
                && $msValue !== ''
                && $msValue !== $enValue
            ) {
                $out[$key] = $msValue;
                continue;
            }

            $out[$key] = $this->translate($enValue);
        }

        return $out;
    }

    private function translate(string $text): string
    {
        if ($this->shouldSkip($text)) {
            return $text;
        }

        if (str_contains($text, ':attribute')) {
            $validation = $this->translateValidation($text);
            if ($validation !== $text) {
                return $validation;
            }
        }

        $translated = $text;

        $phrases = MalayGlossary::phrases();
        uksort($phrases, fn ($a, $b) => strlen($b) <=> strlen($a));
        foreach ($phrases as $en => $ms) {
            $translated = str_replace($en, $ms, $translated);
        }

        $words = MalayGlossary::words();
        uksort($words, fn ($a, $b) => strlen($b) <=> strlen($a));

        foreach ($words as $en => $ms) {
            $translated = preg_replace('/\b' . preg_quote($en, '/') . '\b/u', $ms, $translated) ?? $translated;
        }

        // Common patterns
        $patterns = [
            '/\bCreate ([A-Z][a-zA-Z]+)\b/' => 'Cipta $1',
            '/\bEdit ([A-Z][a-zA-Z]+)\b/' => 'Sunting $1',
            '/\bDelete ([A-Z][a-zA-Z]+)\b/' => 'Padam $1',
            '/\bAdd ([A-Z][a-zA-Z]+)\b/' => 'Tambah $1',
            '/\bNew ([A-Z][a-zA-Z]+)\b/' => '$1 Baharu',
            '/\bAll ([A-Z][a-zA-Z]+)\b/' => 'Semua $1',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $translated = preg_replace($pattern, $replacement, $translated) ?? $translated;
        }

        if ($translated === $text) {
            $translated = $this->translateHeuristics($text);
        }

        return $translated;
    }

    private function translateValidation(string $text): string
    {
        $exact = [
            'The selected :attribute is invalid' => ':attribute yang dipilih tidak sah',
            'The :attribute field is required' => 'Medan :attribute diperlukan',
            'The :attribute must be accepted' => ':attribute mesti diterima',
            'The :attribute must be an array' => ':attribute mesti berupa tatasusunan',
            'The :attribute must be a file' => ':attribute mesti berupa fail',
            'The :attribute must be an image' => ':attribute mesti berupa imej',
            'The :attribute must be a valid email address' => ':attribute mesti alamat e-mel yang sah',
            'The :attribute confirmation does not match' => 'Pengesahan :attribute tidak sepadan',
            'The :attribute field must be true or false' => 'Medan :attribute mesti benar atau palsu',
            'Could not connect to the redis server' => 'Tidak dapat menyambung ke pelayan redis',
        ];

        if (isset($exact[$text])) {
            return $exact[$text];
        }

        $translated = $text;
        $patterns = [
            '/^The :attribute must be a date after :date$/' => ':attribute mesti tarikh selepas :date',
            '/^The :attribute must be a date before :date$/' => ':attribute mesti tarikh sebelum :date',
            '/^The :attribute must be between :min and :max$/' => ':attribute mesti antara :min dan :max',
            '/^The :attribute must be at least :min characters$/' => ':attribute mesti sekurang-kurangnya :min aksara',
            '/^The :attribute may not be greater than :max characters$/' => ':attribute tidak boleh melebihi :max aksara',
            '/^The :attribute must be a valid URL$/' => ':attribute mesti URL yang sah',
            '/^The :attribute format is invalid$/' => 'Format :attribute tidak sah',
            '/^The :attribute must be a file of type: :values$/' => ':attribute mesti fail jenis: :values',
            '/^The :attribute field is required when :values is present$/' => 'Medan :attribute diperlukan apabila :values wujud',
            '/^The :attribute field is required unless :other is in :values$/' => 'Medan :attribute diperlukan melainkan :other dalam :values',
            '/^The :attribute field is required when :other is :value$/' => 'Medan :attribute diperlukan apabila :other ialah :value',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $result = preg_replace($pattern, $replacement, $translated);
            if ($result !== null && $result !== $translated) {
                return $result;
            }
        }

        $generic = [
            'The :attribute must be' => ':attribute mesti',
            'The :attribute may only' => ':attribute hanya boleh',
            'The :attribute field' => 'Medan :attribute',
            'The :attribute is not' => ':attribute bukan',
            'The :attribute has' => ':attribute mempunyai',
            'The :attribute does not' => ':attribute tidak',
        ];

        foreach ($generic as $from => $to) {
            if (str_starts_with($translated, $from)) {
                $translated = $to . substr($translated, strlen($from));
                break;
            }
        }

        return $translated;
    }

    private function translateHeuristics(string $text): string
    {
        $map = [
            '/^Enable the (.+)$/i' => 'Aktifkan $1',
            '/^Disable the (.+)$/i' => 'Nyahaktifkan $1',
            '/^Index (.+)$/i' => 'Indeks $1',
            '/^Create (.+)$/i' => 'Cipta $1',
            '/^Edit (.+)$/i' => 'Sunting $1',
            '/^Delete (.+)$/i' => 'Padam $1',
            '/^View (.+)$/i' => 'Lihat $1',
            '/^Manage (.+)$/i' => 'Urus $1',
            '/^Add (.+)$/i' => 'Tambah $1',
            '/^Update (.+)$/i' => 'Kemas kini $1',
            '/^Save (.+)$/i' => 'Simpan $1',
            '/^Sorry, (.+)$/i' => 'Maaf, $1',
            '/^The (.+) has been created$/i' => '$1 telah dicipta',
            '/^The (.+) has been updated$/i' => '$1 telah dikemas kini',
            '/^The (.+) has been deleted$/i' => '$1 telah dipadam',
            '/^(.+) created successfully\.?$/i' => '$1 berjaya dicipta',
            '/^(.+) updated successfully\.?$/i' => '$1 berjaya dikemas kini',
            '/^(.+) deleted successfully\.?$/i' => '$1 berjaya dipadam',
        ];

        foreach ($map as $pattern => $replacement) {
            $result = preg_replace($pattern, $replacement, $text);
            if ($result !== null && $result !== $text) {
                return $result;
            }
        }

        return $text;
    }

    private function shouldSkip(string $text): bool
    {
        if ($text === '') {
            return true;
        }

        if (preg_match('/^[\d\s\W]+$/', $text)) {
            return true;
        }

        $skip = ['ID', 'SKU', 'API', 'URL', 'CSV', 'ZIP', 'PDF', 'OTP', 'SMS', 'FAQ', 'PWA', 'RTL', 'MYR', 'RM'];
        if (in_array($text, $skip, true)) {
            return true;
        }

        return false;
    }

    /** @param array<string, mixed> $data */
    private function writeArray(string $path, array $data): void
    {
        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $content = "<?php\n\nreturn " . $this->exportArray($data, 0) . ";\n";
        file_put_contents($path, $content);
    }

    /** @param array<string, mixed> $data */
    private function exportArray(array $data, int $depth): string
    {
        $indent = str_repeat('    ', $depth + 1);
        $lines = ["\n"];

        foreach ($data as $key => $value) {
            $exportedKey = is_int($key) ? $key : var_export($key, true);
            if (is_array($value)) {
                $lines[] = "{$indent}{$exportedKey} => " . $this->exportArray($value, $depth + 1) . ',';
                continue;
            }
            $lines[] = "{$indent}{$exportedKey} => " . var_export($value, true) . ',';
        }

        $close = str_repeat('    ', $depth);

        return '[' . implode("\n", $lines) . "\n{$close}]";
    }
}

if ($reformat) {
    (new MsTranslationSync($root, $dryRun, false))->reformatAll();
} else {
    (new MsTranslationSync($root, $dryRun, $force))->run();
}
