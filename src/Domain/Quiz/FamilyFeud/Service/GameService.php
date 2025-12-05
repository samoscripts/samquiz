<?php
// src/Domain/Quiz/FamilyFeud/Service/GameRoundService.php
namespace App\Domain\Quiz\FamilyFeud\Service;

use App\Domain\Quiz\FamilyFeud\Entity\Game;
use App\Domain\Quiz\FamilyFeud\Service\QuestionGenerator;
use App\Domain\Quiz\FamilyFeud\Entity\GamePhase;

class GameService
{
    public function __construct(
        private QuestionGenerator $questionGenerator
    ) {}

    /**
     * Przygotowuje grę do nowej rundy (zwiększa numer rundy, ustawia phase na NEW_ROUND)
     */
    public function prepareNextRound(Game $game): void
    {
        if ($game->getCurrentRound() > $game->getRoundsCount()) {
            throw new \InvalidArgumentException('Game is over - no more rounds');
        }

        // Zwiększ numer rundy
        $game->setCurrentRound($game->getCurrentRound() + 1);
        
        // Ustaw phase na NEW_ROUND
        $game->setPhase(GamePhase::NEW_ROUND);
        
        // Reset stanu rundy (punkty rundy, odkryte odpowiedzi)
        $game->getQuestion()?->flushRevealedAnswers();
        $game->getTeamsCollection()->resetStrikes();
        $game->getTeamsCollection()->getActiveTeam()->addPoints($game->getRoundPoints());

        $game->resetRoundPoints();
        
        // Reset roundPoints - użyj refleksji lub dodaj metodę publiczną jeśli potrzebne
        // Na razie zakładamy że roundPoints jest resetowane w resetRoundState() jeśli istnieje
    }

    public function startNewRound(
        Game $game,
        array $data
    ): void {

        if ($game->getPhase() !== GamePhase::NEW_ROUND) {
            throw new \InvalidArgumentException('Game is not in new round phase');
        }

        if ($game->getCurrentRound() > $game->getRoundsCount()) {
            throw new \InvalidArgumentException('Game is over');
        }

        if (is_null($game->getTeamsCollection())) {
            throw new \InvalidArgumentException('Teams are required');
        }

        $questionText = $data['question'] ?? throw new \InvalidArgumentException('Question is required');
        $answersCount = isset($data['answersCount']) ? (int)$data['answersCount'] : 7;
        // Walidacja zakresu 3-7 (lub 3-10)
        $answersCount = max(3, min(7, $answersCount));
        
        $question = $this->questionGenerator->generate($questionText, $answersCount);
        
        // Reset stanu rundy
        $game->getQuestion()?->flushRevealedAnswers();
        
        // Ustaw pytanie i fazę
        $game->setQuestion($question);
        $game->setAnswersCount($answersCount);
        $game->setPhase(GamePhase::FACE_OFF); // lub PLAYING, zależnie od logiki
    }
}