<?php

namespace App\Domain\Quiz\FamilyFeud\Entity;

use App\Domain\Quiz\FamilyFeud\Entity\Team;
use App\Domain\Quiz\FamilyFeud\Entity\Question;
use App\Domain\Quiz\FamilyFeud\Repository\QuizRepositoryInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use App\Domain\Quiz\FamilyFeud\Entity\TeamCollection;
use App\Domain\Quiz\FamilyFeud\Entity\GamePhase;

class Game
{

    #[Groups(['public'])]
    private ?string $gameId = null;

    #[Ignore]
    private ?Question $question = null;

    #[Groups(['public'])]
    private ?int $answersCount = null;

    #[Groups(['public'])]
    private ?TeamCollection $teams = null;

    #[Groups(['public'])]
    private int $roundPoints = 0;

    #[Groups(['public'])]
    private int $currentRound = 1;

    #[Groups(['public'])]
    private ?GameAnswerCollection $revealedAnswers = null; //odsłonięte odpowiedzi

    #[Groups(['public'])]
    private ?GameAnswerCollection $answersCollection = null; //lista odpowiedzi dla gry (liczba odpowiedzi wskazana 3-10)

    #[Ignore]
    private ?QuizRepositoryInterface $questionRepository = null;

    #[Groups(['public'])]
    private GamePhase $phase = GamePhase::NEW_GAME;
    /**
     * @param Team[] $teams
     */
    public function __construct(?string $gameId = null)
    {
        $this->gameId = $gameId;
    }

    /**
     * Ustawia repozytorium pytań (używane podczas deserializacji)
     */
    public function setQuestionRepository(QuizRepositoryInterface $repository): void
    {
        $this->questionRepository = $repository;
    }

    /**
     * Pobiera gameId
     */
    public function getGameId(): ?string
    {
        return $this->gameId;
    }

    /**
     * Ustawia gameId
     */
    public function setGameId(?string $gameId): void
    {
        $this->gameId = $gameId;
    }

    /**
     * Ustawia answersCount
     */
    public function setAnswersCount(?int $answersCount): void
    {
        $this->answersCount = $answersCount;
    }

    /**
     * Pobiera answersCount
     */
    public function getAnswersCount(): ?int
    {
        return $this->answersCount;
    }

    /**
     * Ustawia currentRound
     */
    public function setCurrentRound(int $currentRound): void
    {
        $this->currentRound = $currentRound;
    }

    /**
     * Pobiera currentRound
     */
    public function getCurrentRound(): int
    {
        return $this->currentRound;
    }

    /**
     * Ustawia revealedAnswers
     */
    public function addRevealedAnswer(GameAnswer $answer): void
    {
        $this->revealedAnswers->add($answer);
    }

    /**
     * Pobiera revealedAnswers
     */
    public function getRevealedAnswers(): ?GameAnswerCollection
    {
        return $this->revealedAnswers;
    }

    public function flushRevealedAnswers(): void
    {
        $this->revealedAnswers = new GameAnswerCollection();
    }

    /**
     * Ustawia answersCollection
     */
    public function setAnswersCollection(?GameAnswerCollection $answersCollection): void
    {
        $this->answersCollection = $answersCollection;
    }

    /**
     * Pobiera answersCollection
     */
    public function getAnswersCollection(): ?GameAnswerCollection
    {
        return $this->answersCollection;
    }

    /**
     * Ustawia phase
     */
    public function setPhase(GamePhase $phase): void
    {
        $this->phase = $phase;
    }

    /**
     * Pobiera phase
     */
    public function getPhase(): GamePhase
    {
        return $this->phase;
    }

    public function getTeams(): ?TeamCollection
    {
        return $this->teams;
    }

    static public function createNewGame(
        string $team1Name, 
        string $team2Name
    ): self
    {
        $gameId = md5(uniqid(rand(), true));
        $teams = new TeamCollection();
        $teams->setTeam1(new Team($team1Name));
        $teams->setTeam2(new Team($team2Name));
        return new self($gameId, $teams);
    }

    public function toArray(): array
    {
        return [
            'gameId' => $this->gameId,
            'teams' => $this->teams->toArray(),
            'phase' => $this->phase->value,
            'currentRound' => $this->currentRound,
        ];
    }
}

