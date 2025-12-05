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
use App\Domain\Quiz\FamilyFeud\ValueObject\GameAlert;
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

    #[Groups(['alert'])]
    private ?GameAlert $gameAlert = null;

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

    public function getRoundPoints(): int
    {
        return $this->roundPoints;
    }

    public function getGameAlert(): ?GameAlert
    {
        return $this->gameAlert;
    }

    public function setGameAlert(GameAlert $gameAlert): void
    {
        $this->gameAlert = $gameAlert;
    }

    public function clearAlert(): void
    {
        $this->gameAlert = null;
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

    /**
     * Odkrywa odpowiedź w fazie END_ROUND (bez weryfikacji, tylko dodaje do revealedAnswers)
     */
    public function revealAnswer(string $answerText): void
    {
        if ($this->phase !== GamePhase::END_ROUND) {
            throw new \InvalidArgumentException('Answer can only be revealed in END_ROUND phase');
        }

        if (!$this->question) {
            throw new \InvalidArgumentException('Question is required');
        }

        // Znajdź odpowiedź w answerCollection
        $answer = $this->question->getAnswerCollection()->getByText($answerText);
        if (!$answer) {
            throw new \InvalidArgumentException('Answer not found in answer collection');
        }

        // Sprawdź czy odpowiedź już nie jest odkryta
        if ($this->question->getRevealedAnswers()->getByText($answerText) !== null) {
            throw new \InvalidArgumentException('Answer is already revealed');
        }

        // Dodaj odpowiedź do revealedAnswers
        $this->question->getRevealedAnswers()->addAnswer($answer);
    }

    public function setRoundPoints(int $roundPoints): void
    {
        $this->roundPoints = $roundPoints;
    }

    public function resetRoundPoints(): void
    {
        $this->roundPoints = 0;
    }
    

    private function handleCorrectAnswer(DomainPlayerAnswer $playerAnswer): void
    {

        //jeżeli odpowiedź już istnieje w kolekcji, to zwróć błąd
        if ($this->question->getRevealedAnswers()->getByText($playerAnswer->getMatchedAnswer()->getText()) !== null) {
            $this->handleIncorrectAnswer();
            return;
        }
        $this->question->getRevealedAnswers()->addAnswer($playerAnswer->getMatchedAnswer() ?? throw new \InvalidArgumentException('Matched answer is required'));
        $this->roundPoints += $playerAnswer->getMatchedAnswer()->points;
        
        // Ustaw alert dla poprawnej odpowiedzi - zawsze ustawiamy CORRECT_SOUND
        // STRIKES_DISPLAY nie jest potrzebny, bo strikes są już wyświetlane w UI automatycznie
        $this->gameAlert = GameAlert::correctSound();

        if ($this->phase === GamePhase::FACE_OFF) {
            //jeżeli odpowiedź jest najwyżej punktowana
            if ($playerAnswer->getMatchedAnswer()->getText() === $this->question->getAnswerCollection()->getFirstAnswer()->getText()) {
                $this->getTeamsCollection()->resetStrikes();
                $this->setPhase(GamePhase::PLAYING);
                return;
            }

            //jeżeli jest to pierwsza odpowiedz, ale nie jest najwyżej punktowaną
            $team1Strikes = $this->teamsCollection->getTeam(TeamCollection::TEAM1_KEY)->getStrikes();
            $team2Strikes = $this->teamsCollection->getTeam(TeamCollection::TEAM2_KEY)->getStrikes();
            $totalStrikes = $team1Strikes + $team2Strikes;
            $revealedAnswersCount = $this->question->getRevealedAnswers()->count();
            if ($revealedAnswersCount === 1 && $playerAnswer->getMatchedAnswer()->getText() !== $this->question->getAnswerCollection()->getFirstAnswer()->getText() && $totalStrikes === 0) {
                $this->teamsCollection->switchActiveTeam();
                return;
            }
            //jeżeli jest to co najmniej 2 próba odpowiedzi (poprzednie mogły ale nie musiały być poprawne)
            //czyli jeżeli suma błędnych odpowiedzi i odsłoniętych odpowiedzi jest równa lub większa niż 2
            if (($totalStrikes + $revealedAnswersCount) >= 2) {
                // jeżeli jest to jedyna odpowiedz
                if ($revealedAnswersCount === 1) {
                    $this->getTeamsCollection()->resetStrikes();
                    $this->setPhase(GamePhase::PLAYING);
                    return;
                }
                //jeżeli odsłoniętych odpowiedzi jest więcej niż 1
                if ($this->question->getRevealedAnswers()->count() > 1) {
                    //jeżeli odpowiedź jest najwyżej punktowaną z odsłoniętych
                    if ($playerAnswer->getMatchedAnswer()->getText() === $this->question->getRevealedAnswers()->getHighestPointsAnswer()) {
                        $this->getTeamsCollection()->resetStrikes();
                        $this->setPhase(GamePhase::PLAYING);
                        return;
                    }
                    else {
                        $this->getTeamsCollection()->resetStrikes();
                        $this->setPhase(GamePhase::PLAYING);
                        //zmien drużynę aktywną
                        $this->teamsCollection->switchActiveTeam();
                        return;
                    }
                }
            }
            return;
        }
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
        $this->teamsCollection->getActiveTeam()->increaseStrikes();
        $activeTeamKey = $this->teamsCollection->activeTeamKey;
        $strikes = $this->teamsCollection->getActiveTeam()->getStrikes();

        // Ustaw alert dla błędu - czerwony X - zawsze ustawiamy ERROR_X
        // STRIKES_DISPLAY nie jest potrzebny, bo strikes są już wyświetlane w UI automatycznie
        $this->gameAlert = GameAlert::errorX();

        //jeżeli faza to playing - to podbicie błędu o 1
        if ($this->phase === GamePhase::PLAYING) {
            if ($strikes >= 3) {
                $this->teamsCollection->switchActiveTeam();
                $this->setPhase(GamePhase::STEAL);
                return;
            }
            return;
        }


        if ($this->phase === GamePhase::FACE_OFF) {
            //jeżeli jest już odsłonięta jakaś odpowiedź
            if ($this->question->getRevealedAnswers()->count() > 0) {
                $this->getTeamsCollection()->resetStrikes();
                $this->setPhase(GamePhase::PLAYING);
            }
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

