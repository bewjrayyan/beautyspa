<?php

use Modules\Account\Services\ConsultationTemplateNormalizer;

it('normalizes question keys options and required state', function () {
    $questions = (new ConsultationTemplateNormalizer())->questions([
        [
            'key' => 'medical condition<script>',
            'label' => ' Medical condition ',
            'type' => 'checkbox',
            'required' => '1',
            'options' => "Allergy\n Asthma \n\nAllergy",
        ],
        [
            'key' => 'heading',
            'label' => ' Section ',
            'type' => 'section',
            'required' => '1',
            'options' => '',
        ],
        ['key' => 'ignored', 'label' => '  ', 'type' => 'text'],
    ]);

    expect($questions)->toHaveCount(2)
        ->and($questions[0]['key'])->toBe('medicalconditionscript')
        ->and($questions[0]['options'])->toBe(['Allergy', 'Asthma'])
        ->and($questions[0]['required'])->toBeTrue()
        ->and($questions[1]['required'])->toBeFalse();
});
