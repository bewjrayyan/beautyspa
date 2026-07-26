@if (count($items ?? []))
    <table class="answer-grid">
        @foreach (array_chunk($items, 2) as $row)
            <tr>
                @foreach ($row as $item)
                    @php
                        $question = $item['question'];
                        $questionLabel = \Modules\Account\Support\ConsultationQuestionLabel::parts($question);
                        $answer = data_get($submission->answers, $question['key']);
                        $displayAnswer = \Modules\Account\Support\ConsultationAnswerPresenter::display($answer);
                    @endphp
                    <td class="answer-cell">
                        <div class="question">
                            <b>{{ $item['number'] }}</b>
                            <span>{{ $questionLabel['primary'] }}</span>
                            @if ($questionLabel['english'])<small lang="en">{{ $questionLabel['english'] }}</small>@endif
                        </div>
                        <div class="response">{{ $displayAnswer }}</div>
                    </td>
                @endforeach
                @if (count($row) === 1)<td class="answer-cell is-empty"></td>@endif
            </tr>
        @endforeach
    </table>
@endif
