<?php

declare(strict_types=1);

namespace App\Controller\Assessment;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class AssessmentAnswersController extends AbstractController
{

    private AssessmentRepository $assessmentRepository;
    private AssessmentService $assessmentService;

    public function __construct(
        AssessmentRepository $assessmentRepository,
        AssessmentService $assessmentService
    ) {
        $this->assessmentRepository = $assessmentRepository;
        $this->assessmentService = $assessmentService;
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

        if (!$instanceId || !$questionId || !$answerOptionId) {
            return new JsonResponse(
                ['error' => 'instance_id '+ $instanceId +', question_id, answer_option_id are required for this request'],
                400,
            );
        }
        // todo create the new service for handling the answers
        // todo check to see if all values given exist
        // todo check the value does not already exist
        // todo add the new entity to database / persist to database
        // todo update the response json to 201 or error if required
        return $this->json("");
    }
}
