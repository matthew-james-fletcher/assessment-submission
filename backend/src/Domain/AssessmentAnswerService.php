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
        $answerOption = null;
        if ($question->getQuestionType() == 'Likert') {
            if (!$answerOptionId) {
                throw new InvalidArgumentException('answer option must be given when question type is "Likert"');
            }
            $answerOption = $this->assessmentAnswerOptionRepository->findAnswerOptionById($answerOptionId);
            if (!$answerOption) {
                throw new InvalidArgumentException('answer option is not found');
            }
        }
        if ($question->getIsReflection() && !$textAnswer) {
            throw new InvalidArgumentException('when question is type "reflection" text answer must be given');
        }
        $previousUserAnswers = $this->assessmentAnswerRepository->findAnswersByInstanceAndQuestion(
            $instance,
            $question,
        );
        if (!empty($previousUserAnswers)) {
            throw new LogicException('this answer already exists');
        }
        // note this could be made into a sql statement as that may be faster, but this is more readable.
        $assessmentQuestion = $instance->getAssessmentQuestion();
        $assessment = $assessmentQuestion->getAssessment();
        $questionsAssessments = $question->getAssessments();
        if (!in_array($assessment, $questionsAssessments)) {
            throw new InvalidArgumentException('you cannot create an answer for a question that is not included on the assessment');
        }
        $answer = new AssessmentAnswer();
        $answer->setAssessmentInstance($instance);
        $answer->setQuestion($question);
        $answer->setAnswerOption($answerOption);
        $answer->setTextAnswer($textAnswer);
        $answer->setNumberValue($numberValue);

        //$entityManager->persist($answer);
        //$entityManager->flush();
    }
}
