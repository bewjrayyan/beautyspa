<?php

use Modules\Account\Services\ConsultationConditionEvaluator;

function conditionalQuestion(string $key, ?array $condition = null): array
{
    return [
        'key' => $key,
        'type' => 'text',
        'condition' => $condition ?? [
            'enabled' => false,
            'source_key' => '',
            'operator' => 'equals',
            'value' => '',
        ],
    ];
}

it('evaluates chained conditions in question order', function () {
    $questions = [
        conditionalQuestion('has_condition'),
        conditionalQuestion('condition_name', [
            'enabled' => true,
            'source_key' => 'has_condition',
            'operator' => 'equals',
            'value' => 'yes',
        ]),
        conditionalQuestion('condition_notes', [
            'enabled' => true,
            'source_key' => 'condition_name',
            'operator' => 'contains',
            'value' => 'skin',
        ]),
    ];

    $visibility = (new ConsultationConditionEvaluator())->visibilityMap($questions, [
        'has_condition' => 'no',
        'condition_name' => 'Skin sensitivity',
    ]);

    expect($visibility)->toBe([
        'has_condition' => true,
        'condition_name' => false,
        'condition_notes' => false,
    ]);
});

it('supports checkbox membership and removes hidden answers', function () {
    $questions = [
        conditionalQuestion('conditions'),
        conditionalQuestion('allergy_details', [
            'enabled' => true,
            'source_key' => 'conditions',
            'operator' => 'contains',
            'value' => 'Allergies',
        ]),
    ];
    $evaluator = new ConsultationConditionEvaluator();

    expect($evaluator->visibleAnswers($questions, [
        'conditions' => ['Asthma'],
        'allergy_details' => 'Injected hidden value',
    ]))->toBe(['conditions' => ['Asthma']]);
});

it('keeps negative conditions hidden until the source has an answer', function () {
    $questions = [
        conditionalQuestion('pregnant'),
        conditionalQuestion('general_advice', [
            'enabled' => true,
            'source_key' => 'pregnant',
            'operator' => 'not_equals',
            'value' => 'yes',
        ]),
    ];

    expect((new ConsultationConditionEvaluator())->visibilityMap($questions, []))
        ->toBe(['pregnant' => true, 'general_advice' => false]);
});

it('matches localized Malay yes and no values with canonical answers', function () {
    $questions = [
        conditionalQuestion('taking_medication'),
        conditionalQuestion('medication_name', [
            'enabled' => true,
            'source_key' => 'taking_medication',
            'operator' => 'equals',
            'value' => 'Ya',
        ]),
    ];

    expect((new ConsultationConditionEvaluator())->visibilityMap($questions, ['taking_medication' => 'yes']))
        ->toBe(['taking_medication' => true, 'medication_name' => true]);
});
