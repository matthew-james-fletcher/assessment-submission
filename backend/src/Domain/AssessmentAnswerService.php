<?php

declare(strict_types=1);

namespace App\Domain;

class AssessmentAnswerService
{
    private AssessmentAnswerOptionRepository $assessmentAnswerOptionRepository;
    public function __construct(
        AssessmentAnswerOptionRepository $assessmentAnswerOptionRepository,
    )
    {
        $this->assessmentAnswerOptionRepository = $assessmentAnswerOptionRepository;
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
        // todo create Answer Option repository
        // todo create assessment answers repository to add into database
    }
}
