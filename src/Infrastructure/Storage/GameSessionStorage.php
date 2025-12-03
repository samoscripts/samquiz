<?php

namespace App\Infrastructure\Storage;

use App\Domain\Quiz\FamilyFeud\Entity\Game;
use App\Domain\Quiz\FamilyFeud\Repository\QuizRepositoryInterface;
use App\Domain\Quiz\FamilyFeud\Service\GameStorageInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Serializer\SerializerInterface;

class GameStorageSession implements GameStorageInterface
{
    private const SESSION_KEY_PREFIX = 'game_session_';

    public function __construct(
        private SessionInterface $session,
        private SerializerInterface $serializer,
        private QuizRepositoryInterface $questionRepository
    ) {
    }

    public function save(string $gameId, Game $game): void
    {
        $key = $this->getSessionKey($gameId);
        
        // Serializuj do JSON, potem dekoduj do tablicy dla sesji
        $json = $this->serializer->serialize($game, 'json', [
            'groups' => ['public']
        ]);
        $data = json_decode($json, true);
        
        $this->session->set($key, $data);
    }

    public function get(string $gameId): ?Game
    {
        $key = $this->getSessionKey($gameId);
        
        if (!$this->session->has($key)) {
            return null;
        }

        $data = $this->session->get($key);
        
        if (!is_array($data)) {
            return null;
        }

        // Konwertuj tablicę z powrotem do JSON dla deserializacji
        $json = json_encode($data);
        
        // Deserializuj z JSON do obiektu Game
        $game = $this->serializer->deserialize($json, Game::class, 'json', [
            'groups' => ['public']
        ]);
        
        // Ustaw repozytorium (nie może być zserializowane)
        $game->setQuestionRepository($this->questionRepository);
        
        return $game;
    }

    public function remove(string $gameId): void
    {
        $key = $this->getSessionKey($gameId);
        $this->session->remove($key);
    }

    public function has(string $gameId): bool
    {
        $key = $this->getSessionKey($gameId);
        return $this->session->has($key);
    }

    private function getSessionKey(string $gameId): string
    {
        return self::SESSION_KEY_PREFIX . $gameId;
    }
}

