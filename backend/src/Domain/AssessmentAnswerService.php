<?php

declare(strict_types=1);

namespace App\Domain;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use LogicException;

class AssessmentAnswerService
{
    private AssessmentAnswerOptionRepository $assessmentAnswerOptionRepository;
    private AssessmentAnswerRepository $assessmentAnswerRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        AssessmentAnswerOptionRepository $assessmentAnswerOptionRepository,
        AssessmentAnswerRepository $assessmentAnswerRepository,
        EntityManagerInterface $entityManager,
    ) {
        $this->assessmentAnswerOptionRepository = $assessmentAnswerOptionRepository;
        $this->assessmentAnswerRepository = $assessmentAnswerRepository;
        $this->entityManager = $entityManager;
    }

    public function createInstancedAnswer(
        AssessmentInstance $instance,
        AssessmentQuestion $question,
        ?string $answerOptionId,
        ?string $textAnswer,
        ?string $numberValue,
    ): AssessmentAnswer {
        $answerOption = null;
        if ($question->getQuestionType() === 'likert') {
            if (!$answerOptionId) {
                throw new InvalidArgumentException('answer option must be given when question type is "likert"');
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

        $assessmentSession = $instance->getSession();
        $assessment = $assessmentSession->getAssessment();
        $questionsAssessments = $question->getAssessments() ?? [];

        if (!in_array($assessment, $questionsAssessments->toArray(), true)) {
            throw new InvalidArgumentException(
                'you cannot create an answer for a question that is not included on the assessment'
            );
        }

        $answer = new AssessmentAnswer(
            null,
            $instance,
            $answerOption,
            $textAnswer,
            $numberValue,
        );

        $this->entityManager->persist($answer);
        $this->entityManager->flush();
        return $answer;
    }

    public function updateInstancedAnswer(
        AssessmentAnswer $answer,
        ?string $answerOptionId,
        ?string $textAnswer,
        ?string $numberValue,
    ): AssessmentAnswer {
        if (!$answer->getAssessmentAnswerOption() && !$answerOptionId) {
            throw new InvalidArgumentException(
                'Cannot change answer option to null if answerOptionId already exists'
            );
        }
        $answerOption = null;
        if ($answerOptionId) {
            $answerOption = $this->assessmentAnswerOptionRepository->findAnswerOptionById($answerOptionId);
            if (!$answerOption) {
                throw new InvalidArgumentException('The answer option given does not exist');
            }
            if (
                $answerOption->getAssessmentQuestion()->getId() !=
                $answer->getAssessmentAnswerOption()->getAssessmentQuestion()->getId()
            ) {
                throw new InvalidArgumentException('Cannot change the question for the answer');
            }
            if ($answerOption->getAssessmentQuestion()->getIsReflection() && !$textAnswer) {
                throw new InvalidArgumentException('when question is type "reflection" text answer must be given');
            }
        }
        $answer->setAssessmentAnswerOption($answerOption);
        $answer->setTextAnswer($textAnswer);
        $answer->setNumericValue($numberValue);
        $this->entityManager->persist($answer);
        $this->entityManager->flush();
        return $answer;
    }
}