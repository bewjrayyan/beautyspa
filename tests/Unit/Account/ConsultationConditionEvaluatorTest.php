<?php

namespace Tests\Unit\Account;

use Modules\Account\Services\ConsultationConditionEvaluator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ConsultationConditionEvaluatorTest extends TestCase
{
    #[Test]
    public function it_evaluates_chained_conditions_in_question_order(): void
    {
    $questions = [
        $this->conditionalQuestion('has_condition'),
        $this->conditionalQuestion('condition_name', [
            'enabled' => true,
            'source_key' => 'has_condition',
            'operator' => 'equals',
            'value' => 'yes',
        ]),
        $this->conditionalQuestion('condition_notes', [
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

        $this->assertSame([
        'has_condition' => true,
        'condition_name' => false,
        'condition_notes' => false,
        ], $visibility);
    }

    #[Test]
    public function it_supports_checkbox_membership_and_removes_hidden_answers(): void
    {
    $questions = [
        $this->conditionalQuestion('conditions'),
        $this->conditionalQuestion('allergy_details', [
            'enabled' => true,
            'source_key' => 'conditions',
            'operator' => 'contains',
            'value' => 'Allergies',
        ]),
    ];
    $evaluator = new ConsultationConditionEvaluator();

        $this->assertSame(['conditions' => ['Asthma']], $evaluator->visibleAnswers($questions, [
        'conditions' => ['Asthma'],
        'allergy_details' => 'Injected hidden value',
        ]));
    }

    #[Test]
    public function it_keeps_negative_conditions_hidden_until_the_source_has_an_answer(): void
    {
    $questions = [
        $this->conditionalQuestion('pregnant'),
        $this->conditionalQuestion('general_advice', [
            'enabled' => true,
            'source_key' => 'pregnant',
            'operator' => 'not_equals',
            'value' => 'yes',
        ]),
    ];

        $this->assertSame(
            ['pregnant' => true, 'general_advice' => false],
            (new ConsultationConditionEvaluator())->visibilityMap($questions, [])
        );
    }

    #[Test]
    public function it_matches_localized_malay_yes_and_no_values_with_canonical_answers(): void
    {
    $questions = [
        $this->conditionalQuestion('taking_medication'),
        $this->conditionalQuestion('medication_name', [
            'enabled' => true,
            'source_key' => 'taking_medication',
            'operator' => 'equals',
            'value' => 'Ya',
        ]),
    ];

        $this->assertSame(
            ['taking_medication' => true, 'medication_name' => true],
            (new ConsultationConditionEvaluator())->visibilityMap($questions, ['taking_medication' => 'yes'])
        );
    }

    private function conditionalQuestion(string $key, ?array $condition = null): array
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
}
