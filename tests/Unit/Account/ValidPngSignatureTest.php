<?php

use Modules\Account\Rules\ValidPngSignature;

$signatureData = function (bool $withInk): string {
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
};

it('accepts a real png signature containing visible ink', function () use ($signatureData) {
    $failed = false;

    (new ValidPngSignature())->validate('signature_data', $signatureData(true), function () use (&$failed) {
        $failed = true;

        return new class {
            public function translate(): void
            {
            }
        };
    });

    expect($failed)->toBeFalse();
});

it('rejects an empty signature canvas and malformed data', function (string $value) {
    $failed = false;

    (new ValidPngSignature())->validate('signature_data', $value, function () use (&$failed) {
        $failed = true;

        return new class {
            public function translate(): void
            {
            }
        };
    });

    expect($failed)->toBeTrue();
})->with([
    'blank canvas' => fn () => $signatureData(false),
    'wrong data URI' => 'data:text/plain;base64,SGVsbG8=',
]);
