<?php

namespace App\Domain\Quiz\FamilyFeud\Entity;

use App\Domain\Quiz\FamilyFeud\Entity\Team;
use App\Domain\Quiz\FamilyFeud\Entity\Question;
use App\Domain\Quiz\FamilyFeud\Repository\QuestionRepositoryInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use App\Domain\Quiz\FamilyFeud\Entity\TeamCollection;
use App\Domain\Quiz\FamilyFeud\Entity\GamePhase;
use App\Domain\Quiz\FamilyFeud\ValueObject\PlayerAnswer as DomainPlayerAnswer;
use Symfony\Component\Serializer\Attribute\SerializedName;

class Game
{

    #[Groups(['public'])]
    private int $roundsCount = 3;

    #[Groups(['public'])]
    private int $currentRound = 0;

    #[Groups(['public'])]
    private ?string $gameId = null;

    #[Groups(['public'])]
    private ?Question $question = null;

    #[Groups(['public'])]
    private ?int $answersCount = null;

    #[Groups(['public'])]
    #[SerializedName('teamsCollection')]
    private ?TeamCollection $teamsCollection = null;

    #[Groups(['public'])]
    private int $roundPoints = 0;


    #[Ignore]
    private ?QuestionRepositoryInterface $questionRepository = null;

    #[Groups(['public'])]
    private GamePhase $phase = GamePhase::NEW_GAME;

    public function __construct(?string $gameId = null)
    {
        $this->gameId = $gameId;
    }

    /**
     * Ustawia repozytorium pytań (używane podczas deserializacji)
     */
        public function setQuestionRepository(QuestionRepositoryInterface $repository): void
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

    public function getTeamsCollection(): ?TeamCollection
    {
        return $this->teamsCollection;
    }

    public function setTeamsCollection(TeamCollection $teamsCollection): void
    {
        $this->teamsCollection = $teamsCollection;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): void
    {
        $this->question = $question;
    }

    public function processPlayerAnswer(DomainPlayerAnswer $playerAnswer): void
    {
        if ($playerAnswer->isCorrect()) {
            $this->handleCorrectAnswer($playerAnswer);
        } else {
            $this->handleIncorrectAnswer();
        }
    }

    private function handleCorrectAnswer(DomainPlayerAnswer $playerAnswer): void
    {
        $this->question->getRevealedAnswers()->addAnswer($playerAnswer->getMatchedAnswer() ?? throw new \InvalidArgumentException('Matched answer is required'));
        $this->roundPoints += $playerAnswer->getMatchedAnswer()->points;

        //jeżeli faza to steal - zmiana phase na endRound i dodanie punktów do drużyny przeciwnj
        if ($this->phase === GamePhase::STEAL) {
            $this->teamsCollection->getActiveTeam()->addPoints($this->roundPoints);
            $this->setPhase(GamePhase::END_ROUND);
        }
        //jeżeli liczba odkrytych odpowiedzi jest równa liczbie odpowiedzi - zmiana phase na completed
        if ($this->question->getRevealedAnswers()->count() === $this->answersCount) {
            $this->teamsCollection->getActiveTeam()->addPoints($this->roundPoints);
            $this->setPhase(GamePhase::END_ROUND);
        }
    }

    private function handleIncorrectAnswer(): void
    {

        //jeżeli faza to playing - to podbicie błędu o 1
        if ($this->phase === GamePhase::PLAYING) {
            $this->teamsCollection->getActiveTeam()->increaseStrikes();
            if ($this->teamsCollection->getActiveTeam()->getStrikes() >= 3) {
                $this->teamsCollection->switchActiveTeam();
                $this->setPhase(GamePhase::STEAL);
                return;
            }
            return;
        }

        //jeżeli faza to faceOff - to tylko zmiana drużyny aktywnej
        if ($this->phase === GamePhase::FACE_OFF) {
            $this->teamsCollection->switchActiveTeam();
            return;
        }
        
        //jeżeli faza to steal - zmiana phase na endRound i dodanie punktów do drużyny przeciwnj
        if ($this->phase === GamePhase::STEAL) {
            $this->teamsCollection->switchActiveTeam();
            $this->teamsCollection->getActiveTeam()->addPoints($this->roundPoints);
            $this->setPhase(GamePhase::END_ROUND);
        }
    }

    static public function createNewGame(
        string $team1Name, 
        string $team2Name,
        int $roundsCount
    ): self
    {
        $gameId = md5(uniqid(rand(), true));
        $game = new self($gameId);
        $game->setRoundsCount($roundsCount);
        $game->setCurrentRound(1);
        $teamsCollection = new TeamCollection();
        $teamsCollection->setTeam1(new Team($team1Name));
        $teamsCollection->setTeam2(new Team($team2Name));

        $game->setTeamsCollection($teamsCollection);
        $game->setPhase(GamePhase::NEW_ROUND);
        return $game;
    }

    public function setRoundsCount(int $roundsCount): void
    {
        $this->roundsCount = $roundsCount;
    }

    public function getRoundsCount(): int
    {
        return $this->roundsCount;
    }

    public function toArray(): array
    {
        $teamsCollection = $this->teamsCollection->toArray();
        return [
            'gameId' => $this->gameId,
            'teamsCollection' => $teamsCollection,
            'phase' => $this->phase->value,
            'currentRound' => $this->currentRound,
        ];
    }
}

