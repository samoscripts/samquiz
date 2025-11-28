<?php

namespace App\Domain\Quiz\FamilyFeud\Entity;

class Team
{
    public function __construct(
        private string $name,
        private int $roundPoints = 0,
        private int $totalPoints = 0,
    ) {}

    public function addRoundPoints(int $points): void
    {
        $this->roundPoints += $points;
    }

    public function endRound(): void
    {
        $this->totalPoints += $this->roundPoints;
        $this->roundPoints = 0;
    }

    public function rename(string $newName): void
    {
        $this->name = $newName;
    }

    // getters
    public function getName(): string 
    { 
        return $this->name; 
    }

    public function getRoundPoints(): int
    { 
        return $this->roundPoints; 
    }
    
    public function getTotalPoints(): int 
    { 
        return $this->totalPoints; 
    }
}
