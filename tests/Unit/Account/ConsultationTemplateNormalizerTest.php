<?php

namespace Tests\Unit\Account;

use Modules\Account\Services\ConsultationTemplateNormalizer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ConsultationTemplateNormalizerTest extends TestCase
{
    #[Test]
    public function it_normalizes_question_keys_options_and_required_state(): void
    {
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

        $this->assertCount(2, $questions);
        $this->assertSame('medicalconditionscript', $questions[0]['key']);
        $this->assertSame(['Allergy', 'Asthma'], $questions[0]['options']);
        $this->assertSame('Select every relevant condition', $questions[0]['placeholder']);
        $this->assertTrue($questions[0]['required']);
        $this->assertSame([
            'enabled' => false,
            'source_key' => '',
            'operator' => 'equals',
            'value' => '',
        ], $questions[0]['condition']);
        $this->assertFalse($questions[1]['required']);
    }

    #[Test]
    public function it_keeps_only_valid_conditions_that_refer_to_an_earlier_question(): void
    {
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

        $this->assertSame([
            'enabled' => true,
            'source_key' => 'taking_medication',
            'operator' => 'equals',
            'value' => 'yes',
        ], $questions[1]['condition']);
        $this->assertFalse($questions[2]['condition']['enabled']);
    }
}
