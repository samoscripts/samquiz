<?php

namespace App\UI\Quiz\Rest\FamilyFeud;

use App\Domain\Quiz\FamilyFeud\Service\AnswerVerifier;
use App\Domain\Quiz\FamilyFeud\Service\QuestionGenerator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Infrastructure\Persistence\Entity\Quiz\FamilyFeud\Question as DoctrineQuestion;

class QuestionController
{
    public function __construct(
    ) {}

    #[Route('/api/family-feud/question/generate', methods: ['POST', 'OPTIONS'])]
    public function generate(Request $request, QuestionGenerator $questionGenerator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $questionText = $data['question'] ?? 'Brak pytania';
        $answersCount = isset($data['answersCount']) ? (int)$data['answersCount'] : 10;
        
        // Walidacja zakresu 3-10
        $answersCount = max(3, min(10, $answersCount));

        $question = $questionGenerator->generate($questionText, $answersCount);
        
        return new JsonResponse($question->toArray());
    }

    #[Route('/api/family-feud/question/{id}/verify', methods: ['POST', 'OPTIONS'])]
    public function verify(
        Request $request, 
        AnswerVerifier $answerVerifier,
        DoctrineQuestion $doctrineQuestion  
    ): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            $playerAnswerText = $data['answer'] ?? '';
            $answersCount = isset($data['answersCount']) ? (int)$data['answersCount'] : 10;
            
            // Walidacja zakresu 3-10
            $answersCount = max(3, min(10, $answersCount));
            
            $playerAnswer = $answerVerifier->findMatchingAnswers(
                $playerAnswerText, 
                $doctrineQuestion,
                $answersCount
            );

            return new JsonResponse($playerAnswer->toArray(), 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
