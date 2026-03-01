<?php

declare(strict_types=1);

namespace App\Controller\Assessment;

use App\Domain\AssessmentInstanceRepository;
use App\Domain\AssessmentAnswerService;
use App\Domain\AssessmentQuestionRepository;
use App\Domain\AssessmentAnswerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use InvalidArgumentException;
use LogicException;

class AssessmentAnswersController extends AbstractController
{
    private AssessmentAnswerService $assessmentAnswerService;
    private AssessmentInstanceRepository $assessmentInstanceRepository;
    private AssessmentQuestionRepository $assessmentQuestionRepository;
    private AssessmentAnswerRepository $assessmentAnswerRepository;

    public function __construct(
        AssessmentAnswerService $assessmentAnswerService,
        AssessmentInstanceRepository $assessmentInstanceRepository,
        AssessmentQuestionRepository $assessmentQuestionRepository,
        AssessmentAnswerRepository $assessmentAnswerRepository,
    ) {
        $this->assessmentAnswerService = $assessmentAnswerService;
        $this->assessmentInstanceRepository = $assessmentInstanceRepository;
        $this->assessmentQuestionRepository = $assessmentQuestionRepository;
        $this->assessmentAnswerRepository = $assessmentAnswerRepository;
    }

    /**
     * @Route("/api/assessment/answers", methods={"POST"})
     */
    public function __create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $instanceId = $data['instance_id'] ?? null;
        $questionId = $data['question_id'] ?? null;
        $answerOptionId = $data['answer_option_id'] ?? null;
        $textAnswer = $data['text_answer'] ?? null;
        $numberValue = $data['number_value'] ?? null;

        if (!$instanceId || !$questionId) {
            return new JsonResponse(
                ['error' => 'instance_id, question_id are required for this request'],
                400,
            );
        }
        $instance = $this->assessmentInstanceRepository->findInstanceById($instanceId);
        if (!$instance) {
            return new JsonResponse(
                ['error' => 'instance given does not exist'],
                404,
            );
        }
        $question = $this->assessmentQuestionRepository->findQuestionById($questionId);
        if (!$question) {
            return new JsonResponse(
                ['error' => 'question given does not exist'],
                404,
            );
        }
        try {
            $this->assessmentAnswerService->createInstancedAnswer(
                $instance,
                $question,
                $answerOptionId,
                $textAnswer,
                $numberValue,
            );
        } catch (InvalidArgumentException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                400
            );
        } catch (LogicException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                409
            );
        }
        return new JsonResponse('Created',201);
    }


    /**
     * @Route("/api/assessment/answers/{id}", methods={"PUT"})
     */
    public function __update(Request $request ,string $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $answerOptionId = $data['answer_option_id'] ?? null;
        $textAnswer = $data['text_answer'] ?? null;
        $numberValue = $data['number_value'] ?? null;
        $answer = $this->assessmentAnswerRepository->findAnswerById($id);
        if (!$answer) {
            return new JsonResponse(
                ['error' => 'answer given does not exist'],
                404,
            );
        }
        try {
            $this->assessmentAnswerService->updateInstancedAnswer(
                $answer,
                $answerOptionId,
                $textAnswer,
                $numberValue,
            );
        } catch (InvalidArgumentException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                400
            );
        } catch (LogicException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                409
            );
        }
        return new JsonResponse('Updated',200);
    }
}
