<?php

namespace Modules\Payment\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use MercadoPago\Payment as MercadoPagoPayment;
use MercadoPago\SDK as MercadoPagoSDK;
use Modules\Order\Entities\Order;
use Modules\Payment\Gateways\PayFast;
use net\authorize\api\constants\ANetEnvironment;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use Modules\Payment\Libraries\SslCommerz\SSLCommerzConfig;
use Modules\Payment\Libraries\SslCommerz\SslCommerzNotification;
use Stripe\StripeClient;

class GatewayPaymentVerifier
{
    /**
     * @throws Exception
     */
    public function verifyStripe(Order $order): string
    {
        $stripe = new StripeClient(['api_key' => setting('stripe_secret_key')]);

        if (setting('stripe_integration_type') === 'embedded_form') {
            $paymentIntentId = request('payment_intent');

            if (! $paymentIntentId) {
                throw new Exception(trans('payment::messages.payment_verification_failed'));
            }

            $intent = $stripe->paymentIntents->retrieve($paymentIntentId);

            if ($intent->status !== 'succeeded') {
                throw new Exception(trans('payment::messages.payment_not_completed'));
            }

            $this->assertAmountMatches($order, (int) $intent->amount);

            return $paymentIntentId;
        }

        $sessionId = request('session_id');

        if (! $sessionId) {
            throw new Exception(trans('payment::messages.payment_verification_failed'));
        }

        $session = $stripe->checkout->sessions->retrieve($sessionId);

        if ($session->payment_status !== 'paid') {
            throw new Exception(trans('payment::messages.payment_not_completed'));
        }

        if ($session->amount_total !== null) {
            $this->assertAmountMatches($order, (int) $session->amount_total);
        }

        return (string) ($session->payment_intent ?? $sessionId);
    }

    /**
     * @throws Exception
     */
    public function verifyPaystack(Order $order): string
    {
        $reference = request('reference');

        if (! $reference) {
            throw new Exception(trans('payment::messages.payment_verification_failed'));
        }

        $response = Http::withToken(setting('paystack_secret_key'))
            ->acceptJson()
            ->get('https://api.paystack.co/transaction/verify/' . $reference);

        if (! $response->successful()) {
            throw new Exception(trans('payment::messages.payment_verification_failed'));
        }

        $data = $response->json('data');

        if (($data['status'] ?? '') !== 'success') {
            throw new Exception(trans('payment::messages.payment_not_completed'));
        }

        $this->assertAmountMatches($order, (int) ($data['amount'] ?? 0), true);

        return $reference;
    }

    /**
     * @throws Exception
     */
    public function verifyPayFast(Order $order): string
    {
        $signature = request('signature');

        if (! $signature) {
            throw new Exception(trans('payment::messages.payment_verification_failed'));
        }

        $payload = request()->except(['signature', 'paymentMethod']);

        $expected = PayFast::generateSignature($payload, setting('payfast_passphrase'));

        if (! hash_equals(strtolower($expected), strtolower($signature))) {
            throw new Exception(trans('payment::messages.payment_verification_failed'));
        }

        if (strtoupper((string) request('payment_status')) !== 'COMPLETE') {
            throw new Exception(trans('payment::messages.payment_not_completed'));
        }

        $paidAmount = (float) request('amount_gross', 0);
        $expectedAmount = (float) $order->total->convertToCurrentCurrency()->amount();

        if (abs($paidAmount - $expectedAmount) > 0.02) {
            throw new Exception(trans('payment::messages.payment_amount_mismatch'));
        }

        return (string) (request('pf_payment_id') ?? request('m_payment_id') ?? $signature);
    }

    /**
     * @throws Exception
     */
    public function verifyFlutterwave(Order $order): string
    {
        $transactionId = request('transaction_id');

        if (! $transactionId) {
            throw new Exception(trans('payment::messages.payment_verification_failed'));
        }

        $response = Http::withToken(setting('flutterwave_secret_key'))
            ->acceptJson()
            ->get('https://api.flutterwave.com/v3/transactions/' . $transactionId . '/verify');

        if (! $response->successful()) {
            throw new Exception(trans('payment::messages.payment_verification_failed'));
        }

        $data = $response->json('data');

        if (($data['status'] ?? '') !== 'successful') {
            throw new Exception(trans('payment::messages.payment_not_completed'));
        }

        $paidAmount = (float) ($data['amount'] ?? 0);
        $expectedAmount = (float) $order->total->convertToCurrentCurrency()->amount();

        if (abs($paidAmount - $expectedAmount) > 0.02) {
            throw new Exception(trans('payment::messages.payment_amount_mismatch'));
        }

        return (string) ($data['tx_ref'] ?? $transactionId);
    }

    /**
     * @throws Exception
     */
    public function verifyInstamojo(Order $order): string
    {
        $paymentId = request('payment_id');

        if (! $paymentId) {
            throw new Exception(trans('payment::messages.payment_verification_failed'));
        }

        $baseUrl = setting('instamojo_test_mode')
            ? 'https://test.instamojo.com/api/1.1/'
            : 'https://www.instamojo.com/api/1.1/';

        $response = Http::withHeaders([
            'X-Api-Key' => setting('instamojo_api_key'),
            'X-Auth-Token' => setting('instamojo_auth_token'),
        ])->get($baseUrl . 'payments/' . $paymentId . '/');

        if (! $response->successful()) {
            throw new Exception(trans('payment::messages.payment_verification_failed'));
        }

        $payment = $response->json('payment') ?? $response->json();

        if (($payment['status'] ?? '') !== 'Credit') {
            throw new Exception(trans('payment::messages.payment_not_completed'));
        }

        return $paymentId;
    }

    /**
     * @throws Exception
     */
    public function verifySslCommerz(Order $order): string
    {
        $valId = request('val_id');
        $tranId = request('tran_id');
        $amount = request('amount');
        $currency = request('currency');

        if (! $valId || ! $tranId) {
            throw new Exception(trans('payment::messages.payment_verification_failed'));
        }

        $config = [
            'sandbox' => setting('sslcommerz_sandbox') ? true : false,
            'store_id' => setting('sslcommerz_store_id') ?? '',
            'store_password' => setting('sslcommerz_store_password') ?? '',
            'is_localhost' => setting('sslcommerz_is_localhost') ? true : false,
            'success_url' => '',
            'fail_url' => '',
        ];

        $sslcommerzConfig = (new SSLCommerzConfig($config))->getConfig();
        $sslc = new SslCommerzNotification($sslcommerzConfig);

        $validated = $sslc->orderValidate(
            request()->all(),
            $tranId,
            $amount ?? $order->total->convertToCurrentCurrency()->round()->amount(),
            $currency ?? currency()
        );

        if (! $validated) {
            throw new Exception(trans('payment::messages.payment_verification_failed'));
        }

        return $valId;
    }

    /**
     * @throws Exception
     */
    public function verifyMercadoPago(Order $order): string
    {
        if (! setting('mercadopago_enabled')) {
            throw new Exception(trans('payment::messages.payment_verification_failed'));
        }

        MercadoPagoSDK::setAccessToken(setting('mercadopago_access_token'));

        $paymentId = request('payment_id') ?? request('collection_id');

        if (! $paymentId) {
            throw new Exception(trans('payment::messages.payment_verification_failed'));
        }

        $payment = MercadoPagoPayment::find_by_id($paymentId);

        if (! $payment || ($payment->status ?? '') !== 'approved') {
            throw new Exception(trans('payment::messages.payment_not_completed'));
        }

        $expectedRef = 'order_' . $order->id;

        if ((string) ($payment->external_reference ?? '') !== $expectedRef) {
            throw new Exception(trans('payment::messages.payment_verification_failed'));
        }

        $paidAmount = (float) ($payment->transaction_amount ?? 0);
        $expectedAmount = (float) $order->total->convertToCurrentCurrency()->amount();

        if (abs($paidAmount - $expectedAmount) > 0.02) {
            throw new Exception(trans('payment::messages.payment_amount_mismatch'));
        }

        return (string) $payment->id;
    }

    /**
     * @throws Exception
     */
    public function verifyPaytm(Order $order): string
    {
        if ((string) request('ORDERID') !== (string) $order->id) {
            throw new Exception(trans('payment::messages.payment_verification_failed'));
        }

        $checksum = request('CHECKSUMHASH');

        if (! $checksum) {
            throw new Exception(trans('payment::messages.payment_verification_failed'));
        }

        require_once base_path('vendor/paytm/js-checkout/lib/PaytmChecksum.php');

        $params = request()->except(['CHECKSUMHASH', 'paymentMethod']);

        if (! verifySignature($params, setting('paytm_merchant_key'), $checksum)) {
            throw new Exception(trans('payment::messages.payment_verification_failed'));
        }

        if (request('STATUS') !== 'TXN_SUCCESS') {
            throw new Exception(trans('payment::messages.payment_not_completed'));
        }

        $paidAmount = (float) request('TXNAMOUNT', 0);
        $expectedAmount = (float) $order->total->convertToCurrentCurrency()->round()->amount();

        if (abs($paidAmount - $expectedAmount) > 0.02) {
            throw new Exception(trans('payment::messages.payment_amount_mismatch'));
        }

        return (string) (request('TXNID') ?? request('ORDERID'));
    }

    /**
     * @throws Exception
     */
    public function verifyAuthorizeNet(Order $order): string
    {
        $transId = request('transId');

        if (! $transId) {
            throw new Exception(trans('payment::messages.payment_verification_failed'));
        }

        if (! in_array((string) request('responseCode'), ['1'], true)) {
            throw new Exception(trans('payment::messages.payment_not_completed'));
        }

        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName(setting('authorizenet_merchant_login_id'));
        $merchantAuthentication->setTransactionKey(setting('authorizenet_merchant_transaction_key'));

        $request = new AnetAPI\GetTransactionDetailsRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setTransId($transId);

        $controller = new AnetController\GetTransactionDetailsController($request);
        $environment = setting('authorizenet_test_mode')
            ? ANetEnvironment::SANDBOX
            : ANetEnvironment::PRODUCTION;

        $response = $controller->executeWithApiResponse($environment);

        if ($response === null || $response->getMessages()->getResultCode() !== 'Ok') {
            throw new Exception(trans('payment::messages.payment_verification_failed'));
        }

        $transaction = $response->getTransaction();

        if ((int) $transaction->getResponseCode() !== 1) {
            throw new Exception(trans('payment::messages.payment_not_completed'));
        }

        $paid = (float) $transaction->getAuthAmount();
        $expected = (float) $order->total->convertToCurrentCurrency()->amount();

        if (abs($paid - $expected) > 0.02) {
            throw new Exception(trans('payment::messages.payment_amount_mismatch'));
        }

        return (string) $transId;
    }

    /**
     * @throws Exception
     */
    public function verifyNagad(string $paymentRefId): void
    {
        $config = [
            'sandbox' => setting('bkash_sandbox') ? true : false,
            'merchant_id' => setting('nagad_merchant_id') ?? '',
            'merchant_number' => setting('nagad_merchant_number') ?? '',
            'public_key' => setting('nagad_public_key') ?? '',
            'private_key' => setting('nagad_private_key') ?? '',
            'callback_url' => '',
        ];

        $nagadPayment = new \Modules\Payment\Libraries\Nagad\NagadPayment($config);
        $response = $nagadPayment->verify($paymentRefId);

        if ($response->statusCode != '000' || $response->status != 'Success') {
            throw new Exception(trans('payment::messages.payment_not_completed'));
        }
    }

    /**
     * @param int $paidAmount smallest currency unit (cents) unless $alreadySubunit is true
     * @throws Exception
     */
    private function assertAmountMatches(Order $order, int $paidAmount, bool $alreadySubunit = false): void
    {
        $expected = $alreadySubunit
            ? (int) $order->total->convertToCurrentCurrency()->subunit()
            : (int) ($order->total->convertToCurrentCurrency()->amount() * 100);

        if ($paidAmount !== $expected) {
            throw new Exception(trans('payment::messages.payment_amount_mismatch'));
        }
    }
}
