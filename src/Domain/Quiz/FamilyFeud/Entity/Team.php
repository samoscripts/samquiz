<?php

namespace App\Domain\Quiz\FamilyFeud\Entity;

use Symfony\Component\Serializer\Attribute\Groups;

class Team
{
    public function __construct(
        #[Groups(['public'])]
        private string $name,
        #[Groups(['public'])]
        private int $totalPoints = 0,
        #[Groups(['public'])]
        private int $strikes = 0,
    ) {}



    public function setName(string $newName): void
    {
        $this->name = $newName;
    }

    // getters
    public function getName(): string 
    { 
        return $this->name; 
    }

    public function getStrikes(): int
    {
        return $this->strikes;
    }

    public function getTotalPoints(): int 
    { 
        return $this->totalPoints; 
    }

    public function addPoints(int $points): void
    {
        $this->totalPoints += $points;
    }
    
    public function increaseStrikes(): void
    {
        $this->strikes++;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'totalPoints' => $this->totalPoints,
        ];
    }
}
