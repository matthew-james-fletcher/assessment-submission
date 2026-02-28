<?php

declare(strict_types=1);

namespace App\Controller\Assessment;

use App\Domain\AssessmentInstanceRepository;
use App\Domain\AssessmentAnswerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class AssessmentAnswersController extends AbstractController
{
    private AssessmentAnswerService $assessmentAnswerService;
    private AssessmentInstanceRepository $assessmentInstanceRepository;

    public function __construct(
        AssessmentAnswerService $assessmentAnswerService,
        AssessmentInstanceRepository $assessmentInstanceRepository,
    ) {
        $this->assessmentAnswerService = $assessmentAnswerService;
        $this->assessmentInstanceRepository = $assessmentInstanceRepository;
    }

    /**
     * @Route("/api/assessment/answers", methods={"POST"})
     */
    public function __invoke(Request $request): JsonResponse
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
                ['error' => 'instance_id is invalid'],
                400,
            );
        }
        // todo create Answer Option repository
        // todo create instance repository
        // todo create assessment question repository
        // todo check the value does not already exist
        return new JsonResponse('',201);
    }
}
