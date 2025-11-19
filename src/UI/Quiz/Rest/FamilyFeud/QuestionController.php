<?php

namespace App\UI\Quiz\Rest\FamilyFeud;

use App\Application\Quiz\FamilyFeud\Command\GenerateQuestionCommand;
use App\Application\Quiz\FamilyFeud\Handler\GenerateQuestionHandler;
use App\Domain\Quiz\FamilyFeud\Service\AnswerVerifier;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class QuestionController
{
    public function __construct(
        private readonly AnswerVerifier $answerVerifier
    ) {}

    #[Route('/api/family-feud/question/generate', methods: ['POST', 'OPTIONS'])]
    public function generate(Request $request, GenerateQuestionHandler $handler): JsonResponse
    {
        // Obsługa preflight request
        if ($request->getMethod() === 'OPTIONS') {
            $response = new JsonResponse();
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'POST, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');
            return $response;
        }

        $data = json_decode($request->getContent(), true);
        $questionText = $data['question'] ?? 'Brak pytania';

        $command = new GenerateQuestionCommand($questionText);
        $result = $handler($command);

        return new JsonResponse($result);
    }

    #[Route('/api/family-feud/question/verify', methods: ['POST', 'OPTIONS'])]
    public function verify(Request $request): JsonResponse
    {
        // Obsługa preflight request
        if ($request->getMethod() === 'OPTIONS') {
            $response = new JsonResponse();
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'POST, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');
            return $response;
        }

        $data = json_decode($request->getContent(), true);
        
        $userAnswer = $data['answer'] ?? '';
        $answers = $data['answers'] ?? [];

        if (empty($userAnswer) || empty($answers) || !is_array($answers)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Brak wymaganych danych',
                'matchingIndices' => []
            ], 400);
        }

        $matchingIndices = $this->answerVerifier->findMatchingAnswers($userAnswer, $answers);

        return new JsonResponse([
            'success' => true,
            'matchingIndices' => $matchingIndices,
            'found' => count($matchingIndices) > 0
        ]);
    }
}
