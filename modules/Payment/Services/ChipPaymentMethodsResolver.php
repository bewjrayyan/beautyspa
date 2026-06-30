<?php

namespace Modules\Payment\Services;

use Modules\Order\Entities\Order;
use Modules\Payment\Libraries\Chip\ChipCollectClient;

class ChipPaymentMethodsResolver
{
    public function __construct(private ChipSurchargeCalculator $fees) {}
    /**
     * @return list<string>
     */
    public function resolveWhitelist(string $gatewayKey): array
    {
        if ($gatewayKey === ChipPaymentMethodConfig::METHOD_ALL) {
            return [];
        }

        $config = ChipPaymentMethodConfig::configFor($gatewayKey);

        if ($config === null) {
            return [];
        }

        $configured = $this->parseWhitelistSetting((string) setting($config['whitelist_setting']));

        if ($configured !== []) {
            return $configured;
        }

        if ($gatewayKey === ChipPaymentMethodConfig::METHOD_CARD) {
            return $this->resolveCardMethods();
        }

        if ($gatewayKey === ChipPaymentMethodConfig::METHOD_ATOME) {
            return $this->resolveAtomeMethods();
        }

        if ($gatewayKey === ChipPaymentMethodConfig::METHOD_EWALLET) {
            return $this->resolveEwalletMethods();
        }

        if ($gatewayKey === ChipPaymentMethodConfig::METHOD_DUITNOW) {
            return $this->resolveDuitnowMethods();
        }

        return $config['default_whitelist'];
    }

    public function surchargeSubunit(string $gatewayKey, ?Order $order = null, ?int $amountSubunit = null): int
    {
        return $this->fees->forGateway($gatewayKey, $order, $amountSubunit);
    }

    /**
     * @return list<string>
     */
    private function resolveCardMethods(): array
    {
        if (! setting('chip_enabled')) {
            return ChipPaymentMethodConfig::configFor(ChipPaymentMethodConfig::METHOD_CARD)['default_whitelist'];
        }

        try {
            $client = new ChipCollectClient(
                (string) setting('chip_brand_id'),
                (string) setting('chip_api_key'),
            );

            $methods = $client->listPaymentMethods(currency());

            if ($methods['card_methods'] !== []) {
                return $methods['card_methods'];
            }

            if ($methods['available_payment_methods'] !== []) {
                return array_values(array_filter(
                    $methods['available_payment_methods'],
                    fn (string $code) => $this->isLikelyCardCode($code)
                ));
            }
        } catch (\Throwable) {
            // Fall back to defaults when API is unreachable.
        }

        return ChipPaymentMethodConfig::configFor(ChipPaymentMethodConfig::METHOD_CARD)['default_whitelist'];
    }


    /**
     * @return list<string>
     */
    private function resolveAtomeMethods(): array
    {
        $defaults = ChipPaymentMethodConfig::configFor(ChipPaymentMethodConfig::METHOD_ATOME)['default_whitelist'];

        if (! setting('chip_enabled')) {
            return $defaults;
        }

        try {
            $client = new ChipCollectClient(
                (string) setting('chip_brand_id'),
                (string) setting('chip_api_key'),
            );

            $methods = $client->listPaymentMethods(currency());
            $available = $methods['available_payment_methods'] ?? [];
            $atomeCodes = array_values(array_filter(
                $available,
                fn (string $code) => str_contains(strtolower($code), 'atome')
            ));

            if ($atomeCodes !== []) {
                return $atomeCodes;
            }
        } catch (\Throwable) {
            // Fall back to defaults when API is unreachable.
        }

        return $defaults;
    }

    /**
     * @return list<string>
     */
    private function resolveEwalletMethods(): array
    {
        $defaults = ChipPaymentMethodConfig::configFor(ChipPaymentMethodConfig::METHOD_EWALLET)['default_whitelist'];

        if (! setting('chip_enabled')) {
            return $defaults;
        }

        try {
            $client = new ChipCollectClient(
                (string) setting('chip_brand_id'),
                (string) setting('chip_api_key'),
            );

            $methods = $client->listPaymentMethods(currency());
            $ewalletCodes = array_values(array_filter(
                $methods['available_payment_methods'] ?? [],
                fn (string $code) => $this->isEwalletCode($code)
            ));

            if ($ewalletCodes !== []) {
                return $ewalletCodes;
            }
        } catch (\Throwable) {
            // Fall back to defaults when API is unreachable.
        }

        return $defaults;
    }

    /**
     * @return list<string>
     */
    private function resolveDuitnowMethods(): array
    {
        $defaults = ChipPaymentMethodConfig::configFor(ChipPaymentMethodConfig::METHOD_DUITNOW)['default_whitelist'];

        if (! setting('chip_enabled')) {
            return $defaults;
        }

        try {
            $client = new ChipCollectClient(
                (string) setting('chip_brand_id'),
                (string) setting('chip_api_key'),
            );

            $methods = $client->listPaymentMethods(currency());
            $duitnowCodes = array_values(array_filter(
                $methods['available_payment_methods'] ?? [],
                fn (string $code) => str_contains(strtolower($code), 'duitnow')
            ));

            if ($duitnowCodes !== []) {
                return $duitnowCodes;
            }
        } catch (\Throwable) {
            // Fall back to defaults when API is unreachable.
        }

        return $defaults;
    }

    private function isLikelyCardCode(string $code): bool
    {
        $lower = strtolower(trim($code));

        if ($lower === '' || $lower === 'fpx' || $lower === 'duitnow_qr') {
            return false;
        }

        if (str_contains($lower, 'atome') || str_starts_with($lower, 'razer_')) {
            return false;
        }

        return true;
    }

    private function isEwalletCode(string $code): bool
    {
        $lower = strtolower(trim($code));

        if (! str_starts_with($lower, 'razer_')) {
            return false;
        }

        return ! str_contains($lower, 'atome') && ! str_contains($lower, 'maybank');
    }

    /**
     * @return list<string>
     */
    private function parseWhitelistSetting(string $value): array
    {
        if ($value === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (string $code) => strtolower(trim($code)),
            preg_split('/[\s,]+/', $value) ?: []
        )));
    }
}
