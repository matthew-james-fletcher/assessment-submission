<?php

declare(strict_types=1);

namespace App\Domain;

class AssessmentAnswerService
{
    private AssessmentAnswerOptionRepository $assessmentAnswerOptionRepository;
    private AssessmentAnswerRepository $assessmentAnswerRepository;
    public function __construct(
        AssessmentAnswerOptionRepository $assessmentAnswerOptionRepository,
        AssessmentAnswerRepository $assessmentAnswerRepository,
    )
    {
        $this->assessmentAnswerOptionRepository = $assessmentAnswerOptionRepository;
        $this->assessmentAnswerRepository = $assessmentAnswerRepository;
    }

    public function createInstancedAnswer(
        AssessmentInstance $instance,
        AssessmentQuestion $question,
        ?string $answerOptionId,
        ?string $textAnswer,
        ?string $numberValue,
    ) {
        // todo add check to see if the value already exists
        // todo add data to database
        // todo create assessment answers repository to add into database
        // todo add check for question to belong to assessment
        $answerOption = null;
        if ($question->getQuestionType() == 'Likert') {
            if (!$answerOptionId) {
                // todo throw error here
            }
            $answerOption = $this->assessmentAnswerOptionRepository->findAnswerOptionById($answerOptionId);
            if (!$answerOption) {
                // todo throw error here
            }
        }
        if ($question->getIsReflection() && !$textAnswer) {
            // todo throw error here
        }

    }
}
