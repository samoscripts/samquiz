<?php

namespace App\Domain\Quiz\FamilyFeud\Service;

use App\Domain\Quiz\FamilyFeud\Entity\Game;

interface GameStorageInterface
{
    /**
     * Zapisuje Game do storage
     */
    public function save(string $gameId, Game $game): void;

    /**
     * Pobiera i deserializuje Game z storage
     */
    public function get(string $gameId): ?Game;

    /**
     * Usuwa Game z storage
     */
    public function remove(string $gameId): void;

    /**
     * Sprawdza czy Game istnieje w storage
     */
    public function has(string $gameId): bool;
}

