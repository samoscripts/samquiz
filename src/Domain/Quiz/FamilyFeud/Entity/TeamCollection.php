<?php

namespace App\Domain\Quiz\FamilyFeud\Entity;

use Symfony\Component\Serializer\Attribute\Groups;
use App\Domain\Quiz\FamilyFeud\Entity\Team;
/**
 * Kolekcja drużyn z metodami pomocniczymi
 */
class TeamCollection
{
    public const TEAM1_KEY = 'team1';
    public const TEAM2_KEY = 'team2';

    #[Groups(['public'])]


    /** @var Team[] */
    private array $teams = [];

    #[Groups(['public'])]
    private ?string $activeTeamKey = null;

    public function __construct(
    ) {}

    public function setTeam1(Team $team): void
    {
        $this->teams[self::TEAM1_KEY] = $team;  
    }

    public function setTeam2(Team $team): void
    {
        $this->teams[self::TEAM2_KEY] = $team;
    }

    public function getTeam(string $key): ?Team
    {
        if ($key !== self::TEAM1_KEY && $key !== self::TEAM2_KEY) {
            throw new \InvalidArgumentException('Invalid team key');
        }

        if(!isset($this->teams[$key])) {
            throw new \InvalidArgumentException('Team not found');
        }

        return $this->teams[$key];
    }

    public function getActiveTeam(): ?Team
    {
        return $this->getTeam($this->activeTeamKey);
    }

    public function setActiveTeamKey(string $key): void
    {
        $this->activeTeamKey = $key;
    }

    public function switchActiveTeam(): void
    {
        $team1 = $this->getTeam(self::TEAM1_KEY);
        $team2 = $this->getTeam(self::TEAM2_KEY);

        $this->getActiveTeam() === $team1 ? $team2 : $team1;
    }

    public function toArray(): array
    {
        return [
            'team1' => $this->teams[self::TEAM1_KEY]->toArray(),
            'team2' => $this->teams[self::TEAM2_KEY]->toArray(),
        ];
    }
}
