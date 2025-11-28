<?php

namespace App\Domain\Quiz\FamilyFeud\Entity;

use App\Domain\Quiz\FamilyFeud\Entity\Team;
use App\Domain\Quiz\FamilyFeud\Entity\Question;
use App\Domain\Quiz\FamilyFeud\Repository\QuizRepositoryInterface;

class GameSession
{
    /**
     * @param Team[] $teams
     */
    public function __construct(
        private string $gameId,
        private int $questionId,
        private Question $question,
        private int $answersCount,
        private array $teams,
        private int $currentRound = 1,
        private array $revealedAnswers = [],
        private GameAnswerCollection $answersCollection,
        private QuizRepositoryInterface $questionRepository
    ) {
        $this->setQuestion();
    }

    private function setQuestion(): void
    {
        $this->question = $this->questionRepository->findById($this->questionId);
    }

    /** @return Team[] */
    public function getTeams(): array
    {
        return $this->teams;
    }

    public function getTeam(string $key): Team
    {
        return $this->teams[$key];
    }

    public function addPoints(string $key, int $points): void
    {
        $this->teams[$key]->addRoundPoints($points);
    }

    public function nextRound(): void
    {
        foreach ($this->teams as $team) {
            $team->endRound();
        }

        $this->currentRound++;
    }
}
