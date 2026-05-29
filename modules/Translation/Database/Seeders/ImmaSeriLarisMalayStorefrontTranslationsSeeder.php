<?php

namespace Modules\Translation\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class ImmaSeriLarisMalayStorefrontTranslationsSeeder extends Seeder
{
    /**
     * Overwrite key storefront Malay strings (after CopyEnglishLangFilesToMalaySeeder).
     *
     * @return void
     */
    public function run(): void
    {
        $this->writeLayouts();
        $this->writeStorefront();
        $this->writeCategories();
        $this->writeCart();
        $this->writeCheckout();
        $this->writeContact();
        $this->writeAccount();

        Cache::tags('translations')->flush();

        $this->command?->info('Malay storefront translations updated.');
    }


    private function writeLayouts(): void
    {
        $path = base_path('modules/Storefront/Resources/lang/ms/layouts.php');

        file_put_contents($path, <<<'PHP'
<?php

return [
    'home' => 'Laman Utama',
    'contact' => 'Hubungi',
    'blog' => 'Blog',
    'compare' => 'Banding',
    'account' => 'Akaun',
    'wishlist' => 'Senarai Hajat',
    'cart' => 'Troli',
    'login_register' => 'Log Masuk / Daftar',
    'login' => 'Log Masuk',
    'search_for_products' => 'Cari rawatan atau produk',
    'category_suggestions' => 'Cadangan Kategori',
    'product_suggestions' => 'Cadangan Produk',
    'more_results' => ':count lagi keputusan',
    'favorites' => 'Kegemaran',
    'most_searched' => 'Paling dicari:',
    'all_categories_header' => 'SEMUA KATEGORI',
    'all_categories' => 'Semua Kategori',
    'navigation' => 'Navigasi',
    'categories' => 'Kategori',
    'menu' => 'Menu',
    'more' => 'Lagi',
    'language_and_currency' => 'Bahasa & Mata Wang',
    'language' => 'Bahasa',
    'currency' => 'Mata Wang',
    'my_cart' => 'Troli Saya',
    'subtotal' => 'Jumlah Kecil',
    'view_cart' => 'Lihat Troli',
    'clear_cart' => 'Kosongkan Troli',
    'checkout' => 'Bayar',
    'subscribe_to_our_newsletter' => 'Langgan Surat Berita Kami',
    'subscribe_to_our_newsletter_subtitle' => 'Langgan surat berita & terima notifikasi promosi.',
    'by_subscribing' => 'Dengan melanggan surat berita anda bersetuju dengan ',
    'privacy_policy' => 'Dasar Privasi.',
    'no_thanks' => 'Tidak, Terima Kasih',
    'email_address' => 'Alamat e-mel',
    'subscribe' => 'Langgan',
    'subscribed' => 'Dilanggan',
    'the_website_uses_cookies' => 'Laman web menggunakan kuki untuk pengalaman terbaik.',
    'accept' => 'Terima',
    'decline' => 'Tolak',
    'next' => 'SETERUSNYA',
    'prev' => 'SEBELUM',
    'contact_us' => 'Hubungi Kami',
    'my_account' => 'Akaun Saya',
    'tags' => 'Tag',
];
PHP
        );
    }


    private function writeStorefront(): void
    {
        $path = base_path('modules/Storefront/Resources/lang/ms/storefront.php');
        $content = file_get_contents(base_path('modules/Storefront/Resources/lang/en/storefront.php'));

        $replacements = [
            "'storefront' => 'Storefront'" => "'storefront' => 'Kedai'",
            "'something_went_wrong' => 'Something went wrong!'" => "'something_went_wrong' => 'Sesuatu tidak kena!'",
            "'try_again' => 'Try Again'" => "'try_again' => 'Cuba Lagi'",
        ];

        file_put_contents($path, str_replace(array_keys($replacements), array_values($replacements), $content));
    }


    private function writeCategories(): void
    {
        file_put_contents(base_path('modules/Storefront/Resources/lang/ms/categories.php'), <<<'PHP'
<?php

return [
    'all_categories' => 'Semua Kategori',
];
PHP
        );
    }


    private function writeCart(): void
    {
        file_put_contents(base_path('modules/Storefront/Resources/lang/ms/cart.php'), <<<'PHP'
<?php

return [
    'cart' => 'Troli',
    'your_cart' => 'Troli Anda',
    'product' => 'Produk',
    'price' => 'Harga',
    'quantity' => 'Kuantiti',
    'total' => 'Jumlah',
    'cart_summary' => 'Ringkasan Troli',
    'subtotal' => 'Jumlah Kecil',
    'coupon' => 'Kupon',
    'apply_coupon' => 'Guna Kupon',
    'proceed_to_checkout' => 'Teruskan ke Bayaran',
    'continue_shopping' => 'Teruskan Membeli-belah',
    'empty_cart' => 'Troli anda kosong.',
    'empty_cart_message' => 'Tiada item dalam troli anda.',
];
PHP
        );
    }


    private function writeCheckout(): void
    {
        file_put_contents(base_path('modules/Storefront/Resources/lang/ms/checkout.php'), <<<'PHP'
<?php

return [
    'checkout' => 'Bayar',
    'billing_details' => 'Maklumat Bil',
    'place_order' => 'Buat Pesanan',
    'order_summary' => 'Ringkasan Pesanan',
];
PHP
        );
    }


    private function writeContact(): void
    {
        file_put_contents(base_path('modules/Storefront/Resources/lang/ms/contact.php'), <<<'PHP'
<?php

return [
    'contact' => 'Hubungi',
    'leave_a_message' => 'Tinggalkan mesej',
    'send_message' => 'Hantar Mesej',
    'your_name' => 'Nama Anda',
    'your_email' => 'E-mel Anda',
    'subject' => 'Subjek',
    'message' => 'Mesej',
];
PHP
        );
    }


    private function writeAccount(): void
    {
        file_put_contents(base_path('modules/Storefront/Resources/lang/ms/account.php'), <<<'PHP'
<?php

return [
    'account' => 'Akaun',
    'dashboard' => 'Papan Pemuka',
    'my_orders' => 'Pesanan Saya',
    'my_wishlist' => 'Senarai Hajat Saya',
    'my_reviews' => 'Ulasan Saya',
    'profile' => 'Profil',
    'logout' => 'Log Keluar',
];
PHP
        );
    }
}
