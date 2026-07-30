<?php

namespace Tests\Feature;

use Modules\Setting\Entities\Setting;
use Modules\Setting\Support\SensitiveSetting;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SensitiveSettingTest extends TestCase
{
    #[Test]
    public function a_sensitive_setting_is_encrypted_and_remains_readable(): void
    {
        $setting = new Setting();
        $setting->key = 'chip_api_key';
        $setting->plain_value = 'secret-value';

        $stored = $setting->getAttributes()['plain_value'];

        $this->assertTrue(SensitiveSetting::isEncrypted($stored));
        $this->assertStringNotContainsString('secret-value', $stored);
        $this->assertSame('secret-value', $setting->value);
    }

    #[Test]
    public function a_legacy_plaintext_sensitive_setting_remains_readable(): void
    {
        $setting = new Setting();
        $setting->setRawAttributes([
            'key' => 'mail_password',
            'is_translatable' => false,
            'plain_value' => serialize('legacy-secret'),
        ]);

        $this->assertSame('legacy-secret', $setting->value);
    }

    #[Test]
    public function secrets_are_removed_from_form_data(): void
    {
        $settings = SensitiveSetting::redactForForm([
            'chip_api_key' => 'secret-value',
            'store_name' => 'Imma Seri Laris',
        ]);

        $this->assertNull($settings['chip_api_key']);
        $this->assertTrue($settings['chip_api_key_configured']);
        $this->assertSame('Imma Seri Laris', $settings['store_name']);
    }
}
