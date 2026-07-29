<?php

namespace Tests\Unit\Account;

use Modules\Account\Rules\ValidPngSignature;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ValidPngSignatureTest extends TestCase
{
    #[Test]
    public function it_accepts_a_real_png_signature_containing_visible_ink(): void
    {
        $this->assertFalse($this->validationFails($this->signatureData(true)));
    }

    #[Test]
    #[DataProvider('invalidSignatureProvider')]
    public function it_rejects_an_empty_signature_canvas_and_malformed_data(string $value): void
    {
        $this->assertTrue($this->validationFails($value));
    }

    public static function invalidSignatureProvider(): array
    {
        return [
            'blank canvas' => [self::signatureData(false)],
            'wrong data URI' => ['data:text/plain;base64,SGVsbG8='],
        ];
    }

    private function validationFails(string $value): bool
    {
        $failed = false;

        (new ValidPngSignature())->validate('signature_data', $value, function () use (&$failed) {
            $failed = true;

            return new class {
                public function translate(): void {}
            };
        });

        return $failed;
    }

    private static function signatureData(bool $withInk): string
    {
        $image = imagecreatetruecolor(240, 100);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);

        if ($withInk) {
            $black = imagecolorallocate($image, 20, 20, 20);
            imagesetthickness($image, 5);
            imageline($image, 20, 70, 215, 25, $black);
        }

        ob_start();
        imagepng($image);
        $png = ob_get_clean();
        imagedestroy($image);

        return 'data:image/png;base64,' . base64_encode($png);
    }
}
