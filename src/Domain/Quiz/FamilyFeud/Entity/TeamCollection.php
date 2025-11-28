<?php

namespace App\Domain\Quiz\FamilyFeud\Entity;

/**
 * Kolekcja drużyn z metodami pomocniczymi
 */
class Teams
{
    /**
     * @param Team[] $teams
     */
    public function __construct(
        private array $teams = []
    ) {
        $this->validateTeams();
    }

    /**
     * Walidacja, że wszystkie elementy są instancjami Team
     */
    private function validateTeams(): void
    {
        foreach ($this->teams as $team) {
            if (!$team instanceof Team) {
                throw new \InvalidArgumentException('All elements must be instances of Team');
            }
        }
    }

    /**
     * Dodaje drużynę do kolekcji
     */
    public function add(Team $team): void
    {
        $this->teams[] = $team;
    }

    /**
     * Usuwa drużynę z kolekcji
     */
    public function remove(Team $team): void
    {
        $this->teams = array_filter(
            $this->teams,
            fn(Team $t) => $t !== $team
        );
        $this->teams = array_values($this->teams); // Reindex array
    }

    /**
     * Pobiera drużynę po indeksie
     */
    public function get(int $index): ?Team
    {
        return $this->teams[$index] ?? null;
    }

    /**
     * Pobiera drużynę po nazwie
     */
    public function getByName(string $name): ?Team
    {
        foreach ($this->teams as $team) {
            if ($team->getName() === $name) {
                return $team;
            }
        }
        return null;
    }

    /**
     * Sprawdza czy drużyna istnieje w kolekcji
     */
    public function contains(Team $team): bool
    {
        return in_array($team, $this->teams, true);
    }

    /**
     * Zwraca liczbę drużyn
     */
    public function count(): int
    {
        return count($this->teams);
    }

    /**
     * Zwraca wszystkie drużyny jako tablicę
     * @return Team[]
     */
    public function all(): array
    {
        return $this->teams;
    }

    /**
     * Zwraca drużynę z najwyższą liczbą punktów w rundzie
     */
    public function getLeaderByRoundPoints(): ?Team
    {
        if (empty($this->teams)) {
            return null;
        }

        $leader = $this->teams[0];
        foreach ($this->teams as $team) {
            if ($team->getRoundPoints() > $leader->getRoundPoints()) {
                $leader = $team;
            }
        }
        return $leader;
    }

    /**
     * Zwraca drużynę z najwyższą liczbą punktów całkowitych
     */
    public function getLeaderByTotalPoints(): ?Team
    {
        if (empty($this->teams)) {
            return null;
        }

        $leader = $this->teams[0];
        foreach ($this->teams as $team) {
            if ($team->getTotalPoints() > $leader->getTotalPoints()) {
                $leader = $team;
            }
        }
        return $leader;
    }

    /**
     * Kończy rundę dla wszystkich drużyn
     */
    public function endRound(): void
    {
        foreach ($this->teams as $team) {
            $team->endRound();
        }
    }

    /**
     * Dodaje punkty do drużyny po indeksie
     */
    public function addPoints(int $index, int $points): void
    {
        $team = $this->get($index);
        if ($team === null) {
            throw new \InvalidArgumentException("Team at index {$index} does not exist");
        }
        $team->addRoundPoints($points);
    }

    /**
     * Dodaje punkty do drużyny po nazwie
     */
    public function addPointsByName(string $name, int $points): void
    {
        $team = $this->getByName($name);
        if ($team === null) {
            throw new \InvalidArgumentException("Team '{$name}' does not exist");
        }
        $team->addRoundPoints($points);
    }
}

