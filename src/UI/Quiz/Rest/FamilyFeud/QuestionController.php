<?php

namespace App\UI\Quiz\Rest\FamilyFeud;

use App\Domain\Quiz\FamilyFeud\Service\AnswerVerifier;
use App\Domain\Quiz\FamilyFeud\Service\QuestionGenerator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Domain\Quiz\FamilyFeud\Entity\Game;
use App\Domain\Quiz\FamilyFeud\Service\GameStorageInterface;
use App\Domain\Quiz\FamilyFeud\Service\GameService;
use Symfony\Component\Serializer\SerializerInterface;

class QuestionController
{
    public function __construct(
        private GameService $gameService,
        private SerializerInterface $serializer
    ) {}


    #[Route('/api/family-feud/game/create', methods: ['POST', 'OPTIONS'])]
    public function createGame(Request $request, GameStorageInterface $gameStorage): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $game = Game::createNewGame(
            $data['team1'],
            $data['team2'],
            $data['roundsCount']
        );

        $gameStorage->save($game->getGameId(), $game);

        // Użyj Symfony Serializer zamiast toArray() - zwraca wszystkie pola z #[Groups(['public'])]
        $serialized = $this->serializer->serialize($game, 'json', [
            'groups' => ['public']
        ]);
        
        return new JsonResponse(json_decode($serialized, true));
    }

    #[Route('/api/family-feud/game/{gameId}/newRound', methods: ['POST', 'OPTIONS'])]
    public function createNewRound(
        Request $request,
        string $gameId,
        GameStorageInterface $gameStorage
        ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $game = $gameStorage->get($gameId);
        if (!$game) {
            return new JsonResponse(['error' => 'Game not found'], 404);
        }
        $this->gameService->startNewRound(
            $game, 
            $data
        );


        $gameStorage->save($game->getGameId(), $game);
        
        // Użyj Symfony Serializer zamiast toArray() - zwraca wszystkie pola z #[Groups(['public'])]
        $serialized = $this->serializer->serialize($game, 'json', [
            'groups' => ['public']
        ]);
        
        return new JsonResponse(json_decode($serialized, true));
    }

    #[Route('/api/family-feud/game/{$gameId}/generateQuestion', methods: ['POST', 'OPTIONS'])]
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

        // Użyj Symfony Serializer zamiast toArray() - zwraca wszystkie pola z #[Groups(['public'])]
        $serialized = $this->serializer->serialize($game, 'json', [
            'groups' => ['public']
        ]);
        
        return new JsonResponse(json_decode($serialized, true));
    }
}
