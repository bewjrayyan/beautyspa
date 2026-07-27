<?php

use Modules\Account\Services\ConsultationTemplateNormalizer;

it('normalizes question keys options and required state', function () {
    $questions = (new ConsultationTemplateNormalizer())->questions([
        [
            'key' => 'medical condition<script>',
            'label' => ' Medical condition ',
            'type' => 'checkbox',
            'placeholder' => ' Select every relevant condition ',
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
        ->and($questions[0]['placeholder'])->toBe('Select every relevant condition')
        ->and($questions[0]['required'])->toBeTrue()
        ->and($questions[0]['condition'])->toBe([
            'enabled' => false,
            'source_key' => '',
            'operator' => 'equals',
            'value' => '',
        ])
        ->and($questions[1]['required'])->toBeFalse();
});

it('keeps only valid conditions that refer to an earlier question', function () {
    $questions = (new ConsultationTemplateNormalizer())->questions([
        ['key' => 'taking_medication', 'label' => 'Taking medication?', 'type' => 'yes_no'],
        [
            'key' => 'medication_name',
            'label' => 'Medication name',
            'type' => 'text',
            'condition' => [
                'enabled' => '1',
                'source_key' => 'taking_medication',
                'operator' => 'equals',
                'value' => 'yes',
            ],
        ],
        [
            'key' => 'invalid_cycle',
            'label' => 'Invalid condition',
            'type' => 'text',
            'condition' => [
                'enabled' => '1',
                'source_key' => 'future_question',
                'operator' => 'equals',
                'value' => 'yes',
            ],
        ],
    ]);

    expect($questions[1]['condition'])->toBe([
        'enabled' => true,
        'source_key' => 'taking_medication',
        'operator' => 'equals',
        'value' => 'yes',
    ])->and($questions[2]['condition']['enabled'])->toBeFalse();
});
