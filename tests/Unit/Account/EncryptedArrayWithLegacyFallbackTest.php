<?php

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Facade;
use Modules\Account\Casts\EncryptedArrayWithLegacyFallback;

beforeEach(function () {
    $container = new Container();
    $container->instance('encrypter', new Encrypter(str_repeat('k', 32), 'AES-256-CBC'));
    Facade::setFacadeApplication($container);
    Facade::clearResolvedInstance('encrypter');
});

afterEach(function () {
    Facade::clearResolvedInstance('encrypter');
    Facade::setFacadeApplication(null);
});

it('encrypts new arrays and decrypts them without exposing medical answers', function () {
    $cast = new EncryptedArrayWithLegacyFallback();
    $model = new class extends Model {};
    $answers = ['medication' => 'yes', 'medication_details' => 'Example medicine'];

    $encrypted = $cast->set($model, 'answers', $answers, []);

    expect($encrypted)
        ->toBeString()
        ->not->toContain('Example medicine')
        ->and($cast->get($model, 'answers', $encrypted, []))
        ->toBe($answers);
});

it('continues to read legacy plain json records', function () {
    $cast = new EncryptedArrayWithLegacyFallback();
    $model = new class extends Model {};
    $legacy = json_encode(['allergies' => ['Latex']], JSON_THROW_ON_ERROR);

expect($cast->get($model, 'answers', $legacy, []))
        ->toBe(['allergies' => ['Latex']]);
});
