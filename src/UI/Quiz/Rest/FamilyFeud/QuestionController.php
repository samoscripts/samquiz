<?php

namespace App\UI\Quiz\Rest\FamilyFeud;

use App\Domain\Quiz\FamilyFeud\Service\AnswerVerifier;
use App\Domain\Quiz\FamilyFeud\Service\QuestionGenerator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Domain\Quiz\FamilyFeud\Entity\Game;
use App\Domain\Quiz\FamilyFeud\Service\GameStorageInterface;

class QuestionController
{
    public function __construct(
    ) {}


    #[Route('/api/family-feud/game/create', methods: ['POST', 'OPTIONS'])]
    public function createGame(Request $request, GameStorageInterface $gameStorage): JsonResponse
    {
        $game = Game::createNewGame(
            $request->request->get('team1'),
            $request->request->get('team2')
        );

        $gameStorage->save($game->getGameId(), $game);

        return new JsonResponse($game->toArray());
    }

    #[Route('/api/family-feud/{$gameId}/generateQuestion', methods: ['POST', 'OPTIONS'])]
    public function generateQuestion(Request $request, QuestionGenerator $questionGenerator, string $gameId, GameStorageInterface $gameStorage): JsonResponse
    {
        $game = $gameStorage->get($gameId);
        if (!$game) {
            return new JsonResponse(['error' => 'Game not found'], 404);
        }
        $data = json_decode($request->getContent(), true);
        $questionText = $data['question'] ?? 'Brak pytania';
        $answersCount = isset($data['answersCount']) ? (int)$data['answersCount'] : 10;
        
        // Walidacja zakresu 3-10
        $answersCount = max(3, min(10, $answersCount));

        $question = $questionGenerator->generate($questionText, $answersCount);
        
        return new JsonResponse($question->toArray());
    }

    #[Route('/api/family-feud/{$gameId}/verifyAnswer', methods: ['POST', 'OPTIONS'])]
    public function verifyAnswer(Request $request, AnswerVerifier $answerVerifier, string $gameId, GameStorageInterface $gameStorage): JsonResponse
    {
        $game = $gameStorage->get($gameId);
        if (!$game) {
            return new JsonResponse(['error' => 'Game not found'], 404);
        }
            
        $playerAnswer = $answerVerifier->findMatchingAnswers(
            $request->request->get('answer'), 
            $game
        );
        $game->processPlayerAnswer($playerAnswer);
        
        $gameStorage->save($game->getGameId(), $game);

        return new JsonResponse($game->toArray());
    }
}
