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

        // Użyj Symfony Serializer - zwraca pola z #[Groups(['public'])] i #[Groups(['alert'])]
        $serialized = $this->serializer->serialize($game, 'json', [
            'groups' => ['public', 'alert']
        ]);
        
        return JsonResponse::fromJsonString($serialized);
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
        
        // Użyj Symfony Serializer - zwraca pola z #[Groups(['public'])] i #[Groups(['alert'])]
        $serialized = $this->serializer->serialize($game, 'json', [
            'groups' => ['public', 'alert']
        ]);
        
        return JsonResponse::fromJsonString($serialized);
    }

    #[Route('/api/family-feud/game/{gameId}/verifyAnswer', methods: ['GET','POST', 'OPTIONS'])]
    public function verifyAnswer(Request $request, AnswerVerifier $answerVerifier, string $gameId, GameStorageInterface $gameStorage): JsonResponse
    {
        $game = $gameStorage->get($gameId);
        if (!$game) {
            return new JsonResponse(['error' => 'Game not found'], 404);
        }
        $data = json_decode($request->getContent(), true);
            
        $playerAnswer = $answerVerifier->findMatchingAnswers(
            $data['answer'], 
            $game
        );
        $game->processPlayerAnswer($playerAnswer);
        
        $gameStorage->save($game->getGameId(), $game);

        // Użyj Symfony Serializer - zwraca pola z #[Groups(['public'])] i #[Groups(['alert'])]
        $serialized = $this->serializer->serialize($game, 'json', [
            'groups' => ['public', 'alert']
        ]);
        
        return JsonResponse::fromJsonString($serialized);
    }

    #[Route('/api/family-feud/game/{gameId}/setActiveTeam', methods: ['POST', 'OPTIONS'])]
    public function setActiveTeam(Request $request, string $gameId, GameStorageInterface $gameStorage): JsonResponse
    {
        $game = $gameStorage->get($gameId);
        if (!$game) {
            return new JsonResponse(['error' => 'Game not found'], 404);
        }
        $data = json_decode($request->getContent(), true);
        $game->getTeamsCollection()->setActiveTeamKey((int)$data['teamId']);
        $gameStorage->save($game->getGameId(), $game);
        $serialized = $this->serializer->serialize($game, 'json', [
            'groups' => ['public', 'alert']
        ]);
        
        return JsonResponse::fromJsonString($serialized);
    }

    #[Route('/api/family-feud/game/{gameId}/nextRound', methods: ['POST', 'OPTIONS'])]
    public function nextRound(Request $request, string $gameId, GameStorageInterface $gameStorage): JsonResponse
    {
        $game = $gameStorage->get($gameId);
        if (!$game) {
            return new JsonResponse(['error' => 'Game not found'], 404);
        }

        try {
            $this->gameService->prepareNextRound($game);
            $gameStorage->save($game->getGameId(), $game);

            $serialized = $this->serializer->serialize($game, 'json', [
                'groups' => ['public', 'alert']
            ]);
            
            return JsonResponse::fromJsonString($serialized);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/api/family-feud/game/{gameId}/revealAnswer', methods: ['POST', 'OPTIONS'])]
    public function revealAnswer(Request $request, string $gameId, GameStorageInterface $gameStorage): JsonResponse
    {
        $game = $gameStorage->get($gameId);
        if (!$game) {
            return new JsonResponse(['error' => 'Game not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $answerText = $data['answerText'] ?? null;

        if (!$answerText) {
            return new JsonResponse(['error' => 'answerText is required'], 400);
        }

        try {
            $game->revealAnswer($answerText);
            $gameStorage->save($game->getGameId(), $game);

            $serialized = $this->serializer->serialize($game, 'json', [
                'groups' => ['public', 'alert']
            ]);
            
            return JsonResponse::fromJsonString($serialized);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}
