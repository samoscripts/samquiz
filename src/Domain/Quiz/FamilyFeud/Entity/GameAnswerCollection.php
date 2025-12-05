<?php

namespace App\Domain\Quiz\FamilyFeud\Entity;

use App\Domain\Quiz\FamilyFeud\Entity\GameAnswer;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * Kolekcja odpowiedzi z metodami pomocniczymi
 */
final class GameAnswerCollection implements \Countable
{
    #[Groups(['public'])]
    private array $answers = [];

    public function __construct(
    ) {}

    private const TOTAL_POINTS = 100;

    /**
     * Walidacja, że wszystkie elementy są instancjami Answer
     * @param array $answers
     */
    private function validateAnswers(array $answers): void
    {
        foreach ($answers as $answer) {
            if (!$answer instanceof GameAnswer) {
                throw new \InvalidArgumentException('All elements must be instances of GameAnswer');
            }
        }
    }

    /**
     * Dodaje odpowiedź do kolekcji
     */
    public function addAnswer(GameAnswer $answer): void
    {
        //sprawdź czy odpowiedź już nie istnieje w kolekcji
        if ($this->getByText($answer->text) !== null) {
            throw new \InvalidArgumentException('Answer already exists');
        }
        $this->answers[] = $answer;
    }

    /**
     * Pobiera odpowiedź po tekście
     */
    public function getByText(string $text): ?GameAnswer
    {
        foreach ($this->answers as $answer) {
            if ($answer->text === $text) {
                return $answer;
            }
        }
        return null;
    }

    /**
     * Sprawdza czy kolekcja jest pusta
     */
    public function isEmpty(): bool
    {
        return empty($this->answers);
    }

    /**
     * Zwraca wszystkie odpowiedzi z kolekcji
     * @return GameAnswer[]
     */
    public function getAnswers(): array
    {
        return $this->answers;
    }


    public function setAnswers(array $answers): void
    {
        $this->validateAnswers($answers);
        $this->answers = $answers;
    }

    /**
     * Zwraca nową kolekcję z ograniczoną liczbą odpowiedzi
     */
    public function limit(int $count): self
    {
        $answersCollection = new self();
        $answersCollection->setAnswers(array_slice($this->answers, 0, $count));
        return $answersCollection;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->answers);
    }

    public function count(): int
    {
        return count($this->answers);
    }

    /**
     * Zwraca pierwszą odpowiedź ze stosu (pierwszą dodaną do kolekcji)
     * W kontekście revealedAnswers - zwraca pierwszą odkrytą odpowiedź
     * Niekoniecznie musi być to odpowiedź z indeksu 0 w oryginalnej kolekcji
     * Przykład: jeśli odkryto najpierw odpowiedź #4, to zwróci odpowiedź #4
     */
    public function getFirstAnswer(): ?GameAnswer
    {
        // Zwraca pierwszą odpowiedź w kolejności dodania (pierwszą ze stosu)
        // W revealedAnswers - pierwsza odkryta odpowiedź (może być z dowolnej pozycji w oryginalnej kolekcji)
        return $this->answers[0] ?? null;
    }

    /**
     * Zwraca najwyżej punktowaną odpowiedź z kolekcji
     */
    public function getHighestPointsAnswer(): ?GameAnswer
    {
        if ($this->isEmpty()) {
            return null;
        }

        $highest = $this->answers[0];
        foreach ($this->answers as $answer) {
            if ($answer->points > $highest->points) {
                $highest = $answer;
            }
        }

        return $highest;
    }

    /**
     * Sumuje punkty wszystkich odpowiedzi
     */
    public function totalPoints(): int
    {
        return array_sum(
            array_map(fn(GameAnswer $a) => $a->points, $this->answers)
        );
        
    }

    /**
     * Zwraca nową kolekcję z przeliczeniem punktów tak,
     * aby suma wynosiła 100 (proporcjonalnie).
     * 
     * @throws \RuntimeException Jeśli suma punktów wynosi 0 lub kolekcja jest pusta
     */
    public function recalculateAnswersPoints(): self
    {
        if ($this->isEmpty()) {
            throw new \RuntimeException('Cannot normalize empty collection');
        }

        $currentTotalPoints = $this->totalPoints();

        if ($currentTotalPoints === 0) {
            throw new \RuntimeException('Cannot normalize collection with zero total points');
        }

        $normalized = [];

        foreach ($this->answers as $answer) {
            $percent = ($answer->points / $currentTotalPoints) * self::TOTAL_POINTS;
            $points = (int) round($percent);
            $normalized[] = new GameAnswer($answer->text, $points);
        }

        // Korekta sumy, aby wynosiła dokładnie tyle co w parametrze self::TOTAL_POINTS
        $diff = self::TOTAL_POINTS - $currentTotalPoints;

        if ($diff !== 0 && count($normalized) > 0) {
            // Dodaj brakujący punkt/punkty do ostatniego elementu (bardziej naturalne)
            /** @var GameAnswer $last */
            $last = $normalized[count($normalized) - 1];
            $normalized[count($normalized) - 1] = new GameAnswer($last->text, $last->points + $diff);
        }
        $answersCollection = new self();
        $answersCollection->setAnswers($normalized);
        return $answersCollection;
    }

    public function toArray(): array
    {
        return array_map(fn(GameAnswer $a) => [
            'text' => $a->text,
            'points' => $a->points
        ], $this->answers);
    }
}
