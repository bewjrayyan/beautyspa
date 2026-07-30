<?php

namespace Tests\Feature;

use Composer\InstalledVersions;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Laravel\Socialite\Two\GoogleProvider;
use Modules\Category\Entities\Category;
use Modules\Menu\Entities\MenuItem;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TypiCMS\NestableCollection;

class Laravel12CompatibilityTest extends TestCase
{
    #[Test]
    public function the_application_boots_on_the_patched_laravel_12_release(): void
    {
        $this->assertSame('12', explode('.', Application::VERSION, 2)[0]);
        $this->assertTrue(version_compare(Application::VERSION, '12.61.1', '>='));
    }

    #[Test]
    public function upgraded_authentication_and_datatable_packages_are_installed(): void
    {
        $this->assertTrue(version_compare(
            InstalledVersions::getVersion('cartalyst/sentinel'),
            '9.0.0',
            '>='
        ));
        $this->assertTrue(version_compare(
            InstalledVersions::getVersion('yajra/laravel-datatables-oracle'),
            '12.0.0',
            '>='
        ));
        $this->assertTrue(version_compare(
            InstalledVersions::getVersion('laravel/socialite'),
            '5.27.0',
            '>='
        ));
        $this->assertTrue(version_compare(
            InstalledVersions::getVersion('firebase/php-jwt'),
            '7.1.0',
            '>='
        ));
    }

    #[Test]
    public function socialite_verifies_a_google_id_token_with_php_jwt_7(): void
    {
        $originalRandomFile = getenv('RANDFILE');
        putenv('RANDFILE='.sys_get_temp_dir().'/fleetcart-socialite-openssl.rnd');

        try {
            $key = openssl_pkey_new([
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ]);
        } finally {
            $originalRandomFile === false
                ? putenv('RANDFILE')
                : putenv('RANDFILE='.$originalRandomFile);
        }

        $this->assertNotFalse($key);

        $details = openssl_pkey_get_details($key);

        $this->assertIsArray($details);

        $keyId = 'socialite-test-key';
        $clientId = 'socialite-test-client';
        $jwks = [
            'keys' => [[
                'kty' => 'RSA',
                'alg' => 'RS256',
                'use' => 'sig',
                'kid' => $keyId,
                'n' => JWT::urlsafeB64Encode($details['rsa']['n']),
                'e' => JWT::urlsafeB64Encode($details['rsa']['e']),
            ]],
        ];
        $token = JWT::encode([
            'iss' => 'https://accounts.google.com',
            'aud' => $clientId,
            'sub' => 'social-user-123',
            'email' => 'social-user@example.test',
            'iat' => time(),
            'exp' => time() + 300,
        ], $key, 'RS256', $keyId);

        $provider = new class(Request::create('/'), $clientId, 'secret', 'https://example.test/callback', $jwks) extends GoogleProvider
        {
            public function __construct(
                Request $request,
                string $clientId,
                string $clientSecret,
                string $redirectUrl,
                private readonly array $jwks,
            ) {
                parent::__construct($request, $clientId, $clientSecret, $redirectUrl);
            }

            public function verifyIdToken(string $token): array
            {
                return $this->getUserFromJwtToken($token);
            }

            protected function getGoogleJwks(): array
            {
                return $this->jwks;
            }
        };

        $claims = $provider->verifyIdToken($token);

        $this->assertSame('social-user-123', $claims['sub']);
        $this->assertSame('social-user@example.test', $claims['email']);
    }

    #[Test]
    public function retired_payment_sdk_packages_are_not_installed(): void
    {
        foreach ([
            'authorizenet/authorizenet',
            'instamojo/instamojo-php',
            'iyzico/iyzipay-php',
            'mercadopago/dx-php',
            'paypal/paypal-checkout-sdk',
            'paypal/paypalhttp',
            'paytm/js-checkout',
            'razorpay/razorpay',
            'stripe/stripe-php',
            'yabacon/paystack-php',
        ] as $package) {
            $this->assertFalse(InstalledVersions::isInstalled($package), "{$package} must remain retired.");
        }

        $this->assertFalse(class_exists(\PayPalCheckoutSdk\Core\PayPalHttpClient::class));
        $this->assertFalse(InstalledVersions::isInstalled('doctrine/annotations'));
        $this->assertFalse(class_exists(\MercadoPago\SDK::class));
        $this->assertFalse(class_exists(\Modules\Payment\Gateways\MercadoPago::class));

        foreach ([
            \Modules\Payment\Gateways\AuthorizeNet::class,
            \Modules\Payment\Gateways\Bkash::class,
            \Modules\Payment\Gateways\CheckPayment::class,
            \Modules\Payment\Gateways\Flutterwave::class,
            \Modules\Payment\Gateways\Instamojo::class,
            \Modules\Payment\Gateways\Iyzico::class,
            \Modules\Payment\Gateways\Nagad::class,
            \Modules\Payment\Gateways\PayFast::class,
            \Modules\Payment\Gateways\Paystack::class,
            \Modules\Payment\Gateways\Paytm::class,
            \Modules\Payment\Gateways\Razorpay::class,
            \Modules\Payment\Gateways\SslCommerz::class,
            \Modules\Payment\Gateways\Stripe::class,
        ] as $gateway) {
            $this->assertFalse(class_exists($gateway), "{$gateway} must remain retired.");
        }

        $this->assertTrue(class_exists(\Modules\Payment\Gateways\COD::class));
        $this->assertTrue(class_exists(\Modules\Payment\Gateways\BankTransfer::class));
        $this->assertTrue(class_exists(\Modules\Payment\Gateways\ChipGateway::class));
    }

    #[Test]
    public function category_and_menu_models_still_use_nestable_collections(): void
    {
        $this->assertInstanceOf(NestableCollection::class, (new Category())->newCollection());
        $this->assertInstanceOf(NestableCollection::class, (new MenuItem())->newCollection());
    }
}
