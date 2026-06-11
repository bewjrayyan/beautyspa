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
                    fn (string $code) => ! in_array($code, ['fpx', 'atome'], true)
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
