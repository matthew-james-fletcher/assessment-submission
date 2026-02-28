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
        // todo add data to database
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
        $previousUserAnswers = $this->assessmentAnswerRepository->findAnswersByInstanceAndQuestion(
            $instance->getId(),
            $question->getId()
        )
        if (!empty($previousUserAnswers)) {
            // todo throw error here
        }
        // note this could be made into a sql statement as that may be faster, but this is more readable.
        $assessmentQuestion = $instance->getAssessmentQuestion();
        $assessment = $assessmentQuestion->getAssessment();
        $questionsAssessments = $question->getAssessments();
        if (!in_array($assessment, $questionsAssessments)) {
            // todo throw error
        }
    }
}
