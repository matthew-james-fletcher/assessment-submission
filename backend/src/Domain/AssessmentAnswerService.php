<?php

declare(strict_types=1);

namespace App\Domain;

class AssessmentService
{
    public function __construct()
    {
    }

    public function createInstancedAnswer(
        AssessmentInstance $instance,
        AssessmentQuestion $question,
        ?AssessmentAnswerOption $answerOption,
        ?string $textAnswer,
        ?string $numberValue,
    ) {
        // todo add check to see if the value already exists
        // todo add data to database
        // todo create assessment answers repository to add into database
    }
}
