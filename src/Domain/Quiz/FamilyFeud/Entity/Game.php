<?php

namespace App\Domain\Quiz\FamilyFeud\Entity;

use App\Domain\Quiz\FamilyFeud\Entity\Team;
use App\Domain\Quiz\FamilyFeud\Entity\Question;
use App\Domain\Quiz\FamilyFeud\Repository\QuestionRepositoryInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use App\Domain\Quiz\FamilyFeud\Entity\TeamCollection;
use App\Domain\Quiz\FamilyFeud\Entity\GamePhase;
use App\Domain\Quiz\FamilyFeud\Service\AnswerVerifier;
use App\Domain\Quiz\FamilyFeud\ValueObject\PlayerAnswer as DomainPlayerAnswer;

class Game
{

    #[Groups(['public'])]
    private ?string $gameId = null;

    #[Groups(['public'])]
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
    private ?QuestionRepositoryInterface $questionRepository = null;

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

    public function handleCorrectAnswer(DomainPlayerAnswer $playerAnswer): void
    {
        $this->addRevealedAnswer($playerAnswer->getMatchedAnswer());
        $this->roundPoints += $playerAnswer->getMatchedAnswer()->getPoints();

        //jeżeli faza to steal - zmiana phase na endRound i dodanie punktów do drużyny przeciwnj
        if ($this->phase === GamePhase::STEAL) {
            $this->teams->getActiveTeam()->addPoints($this->roundPoints);
            $this->setPhase(GamePhase::END_ROUND);
        }
        //jeżeli liczba odkrytych odpowiedzi jest równa liczbie odpowiedzi - zmiana phase na completed
        if ($this->revealedAnswers->count() === $this->answersCount) {
            $this->teams->getActiveTeam()->addPoints($this->roundPoints);
            $this->setPhase(GamePhase::END_ROUND);
        }
    }

    public function handleIncorrectAnswer(): void
    {

        //jeżeli faza to playing - to podbicie błędu o 1
        if ($this->phase === GamePhase::PLAYING) {
            $this->teams->getActiveTeam()->increaseStrikes();
            if ($this->teams->getActiveTeam()->getStrikes() >= 3) {
                $this->teams->switchActiveTeam();
                $this->setPhase(GamePhase::STEAL);
                return;
            }
            return;
        }


        
        //jeżeli faza to faceOff - to tylko zmiana drużyny aktywnej
        if ($this->phase === GamePhase::FACE_OFF) {
            $this->teams->switchActiveTeam();
            return;
        }
        
        //jeżeli faza to steal - zmiana phase na endRound i dodanie punktów do drużyny przeciwnj
        if ($this->phase === GamePhase::STEAL) {
            $this->teams->switchActiveTeam();
            $this->teams->getActiveTeam()->addPoints($this->roundPoints);
            $this->setPhase(GamePhase::END_ROUND);
        }
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

