<?php

namespace App\Infrastructure\Storage;

use App\Domain\Quiz\FamilyFeud\Entity\Game;
use App\Domain\Quiz\FamilyFeud\Repository\QuestionRepositoryInterface;
use App\Domain\Quiz\FamilyFeud\Service\GameStorageInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class GameStorageSession implements GameStorageInterface
{
    private const SESSION_KEY_PREFIX = 'game_session_';

    private SessionInterface $session;

    public function __construct(
        private RequestStack $requestStack,
        private SerializerInterface $serializer,
        private QuestionRepositoryInterface $questionRepository
    ) {
        $this->session = $this->requestStack->getSession();
    }

    /**
     * Pobiera DenormalizerInterface z SerializerInterface
     */
    private function getDenormalizer(): DenormalizerInterface
    {
        if (!$this->serializer instanceof DenormalizerInterface) {
            throw new \RuntimeException('Serializer must implement DenormalizerInterface');
        }
        return $this->serializer;
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

        $data = $this->session->get($key) ?? [];
        $data = $this->session->get($key);
        
        if (!is_array($data)) {
            return null;
        }

        // Użyj denormalize() zamiast deserialize() - lepsze dla zagnieżdżonych obiektów
        // denormalize() działa bezpośrednio na tablicy, więc lepiej radzi sobie ze złożonymi strukturami
        $game = $this->getDenormalizer()->denormalize($data, Game::class, 'json', [
            AbstractNormalizer::GROUPS => ['public'],
            AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => true,
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

