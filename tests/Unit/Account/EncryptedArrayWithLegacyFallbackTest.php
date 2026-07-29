<?php

namespace Tests\Unit\Account;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Facade;
use Modules\Account\Casts\EncryptedArrayWithLegacyFallback;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EncryptedArrayWithLegacyFallbackTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $container = new Container();
        $container->instance('encrypter', new Encrypter(str_repeat('k', 32), 'AES-256-CBC'));
        Facade::setFacadeApplication($container);
        Facade::clearResolvedInstance('encrypter');
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstance('encrypter');
        Facade::setFacadeApplication(null);

        parent::tearDown();
    }

    #[Test]
    public function it_encrypts_new_arrays_and_decrypts_them_without_exposing_medical_answers(): void
    {
        $cast = new EncryptedArrayWithLegacyFallback();
        $model = new class extends Model {};
        $answers = ['medication' => 'yes', 'medication_details' => 'Example medicine'];

        $encrypted = $cast->set($model, 'answers', $answers, []);

        $this->assertIsString($encrypted);
        $this->assertStringNotContainsString('Example medicine', $encrypted);
        $this->assertSame($answers, $cast->get($model, 'answers', $encrypted, []));
    }

    #[Test]
    public function it_continues_to_read_legacy_plain_json_records(): void
    {
        $cast = new EncryptedArrayWithLegacyFallback();
        $model = new class extends Model {};
        $legacy = json_encode(['allergies' => ['Latex']], JSON_THROW_ON_ERROR);

        $this->assertSame(['allergies' => ['Latex']], $cast->get($model, 'answers', $legacy, []));
    }
}
