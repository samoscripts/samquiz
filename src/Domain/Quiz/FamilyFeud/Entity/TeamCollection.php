<?php

namespace App\Domain\Quiz\FamilyFeud\Entity;

use Symfony\Component\Serializer\Attribute\Groups;
use App\Domain\Quiz\FamilyFeud\Entity\Team;
/**
 * Kolekcja drużyn z metodami pomocniczymi
 */
final class TeamCollection
{
    public const TEAM1_KEY = 1;
    public const TEAM2_KEY = 2;



    #[Groups(['public'])]
    public array $teams = [];

    #[Groups(['public'])]
    public ?int $activeTeamKey = null;

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

    public function getTeam(int $key): ?Team
    {
        if ($key !== self::TEAM1_KEY && $key !== self::TEAM2_KEY) {
            throw new \InvalidArgumentException('Invalid team key');
        }

        if(!isset($this->teams[$key])) {
            throw new \InvalidArgumentException('Team not found');
        }

        return $this->teams[$key];
    }

    public function addTeam(Team $team): void
    {
        $this->teams[] = $team;
    }

    public function getTeams(): array
    {
        return $this->teams;
    }

    public function setTeams(array $teams): void
    {
        $this->teams = $teams;
    }

    public function getActiveTeam(): ?Team
    {

        if(!is_null($this->activeTeamKey) && in_array($this->activeTeamKey, [self::TEAM1_KEY, self::TEAM2_KEY])) {
            return $this->getTeam($this->activeTeamKey);
        }
        return null;
    }

    public function setActiveTeamKey(?int $key): void
    {
        if(!is_null($key) && !in_array($key, [self::TEAM1_KEY, self::TEAM2_KEY])) {
            throw new \InvalidArgumentException('Invalid team key');
        }
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
        $team1 = $this->teams[self::TEAM1_KEY]->toArray();
        $team2 = $this->teams[self::TEAM2_KEY]->toArray();
        return [
            'team1' => $team1,
            'team2' => $team2,
        ];
    }
}
