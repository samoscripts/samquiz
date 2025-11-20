<?php

namespace App\UI\Quiz\Rest\FamilyFeud;

use App\Domain\Quiz\FamilyFeud\Service\AnswerVerifier;
use App\Domain\Quiz\FamilyFeud\Service\QuestionGenerator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class QuestionController
{
    public function __construct(
    ) {}

    #[Route('/api/family-feud/question/generate', methods: ['POST', 'OPTIONS'])]
    public function generate(Request $request, QuestionGenerator $questionGenerator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $questionText = $data['question'] ?? 'Brak pytania';

        $question = $questionGenerator->generate($questionText);
        
        return new JsonResponse($question->toArray());
    }

    #[Route('/api/family-feud/question/verify', methods: ['POST', 'OPTIONS'])]
    public function verify(Request $request, AnswerVerifier $answerVerifier): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            $userAnswer = $data['answer'] ?? '';
            $answers = $data['answers'] ?? [];
            $playerAnswer = $answerVerifier->findMatchingAnswers($userAnswer, $answers);

            return new JsonResponse($playerAnswer->toArray(), 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
