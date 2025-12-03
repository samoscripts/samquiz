<?php

namespace App\Domain\Quiz\FamilyFeud\Entity;

use App\Domain\Quiz\FamilyFeud\Entity\GameAnswer;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * Kolekcja odpowiedzi z metodami pomocniczymi
 */
final class GameAnswerCollection implements \IteratorAggregate, \Countable
{
    /** @var GameAnswer[] */
    #[Groups(['public'])]
    private array $answers = [];

    private const TOTAL_POINTS = 100;

    /**
     * @param GameAnswer[] $answers
     */
    public function __construct(array $answers = [], $count = null)
    {
        if (!empty($answers)) {
            $this->validateAnswers($answers);
        }
        
        // Jeśli $count nie jest podane, użyj count($answers)
        if ($count === null) {
            $count = count($answers);
        }
        
        // Użyj min($count, count($answers)) aby uniknąć błędów indeksowania
        $limit = min($count, count($answers));
        
        for($i = 0; $i < $limit; $i++) {
            $this->answers[] = new GameAnswer($answers[$i]->text, $answers[$i]->points);
        }
        
        if (!empty($this->answers)) {
            $this->recalculateAnswersPoints();
        }
    }

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
    public function add(GameAnswer $answer): void
    {
        $this->answers[] = $answer;
    }

    /**
     * Usuwa odpowiedź z kolekcji
     */
    public function remove(GameAnswer $answer): void
    {
        $this->answers = array_filter(
            $this->answers,
            fn(GameAnswer $a) => $a !== $answer
        );
        $this->answers = array_values($this->answers); // Reindex array
    }

    /**
     * Pobiera odpowiedź po indeksie
     */
    public function get(int $index): ?GameAnswer
    {
        return $this->answers[$index] ?? null;
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
     * Sprawdza czy odpowiedź istnieje w kolekcji
     */
    public function contains(GameAnswer $answer): bool
    {
        return in_array($answer, $this->answers, true);
    }

    /**
     * Sprawdza czy kolekcja jest pusta
     */
    public function isEmpty(): bool
    {
        return empty($this->answers);
    }

    /**
     * Zwraca pierwszą odpowiedź
     */
    public function first(): ?GameAnswer
    {
        return $this->answers[0] ?? null;
    }

    /**
     * Zwraca ostatnią odpowiedź
     */
    public function last(): ?GameAnswer
    {
        if (empty($this->answers)) {
            return null;
        }
        return $this->answers[count($this->answers) - 1];
    }

    /**
     * Zwraca wszystkie odpowiedzi jako tablicę
     * @return GameAnswer[]
     */
    public function all(): array
    {
        return $this->answers;
    }

    /**
     * Getter dla serializera
     * @return GameAnswer[]
     */
    
    public function getAnswers(): array
    {
        return $this->answers;
    }

    /**
     * Setter dla deserializera
     * @param GameAnswer[] $answers
     */
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
        return new self(array_slice($this->answers, 0, $count));
    }

    /**
     * Zwraca nową kolekcję z wyciętym fragmentem
     */
    public function slice(int $offset, ?int $length = null): self
    {
        return new self(array_slice($this->answers, $offset, $length));
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

        return new self($normalized);
    }

    public function toArray(): array
    {
        return array_map(fn(GameAnswer $a) => [
            'text' => $a->text,
            'points' => $a->points,
            'hidden' => $a->isHidden(),
        ], $this->answers);
    }

    public static function fromArray(array $data): self
    {
        if (empty($data)) {
            return new self([], 0);
        }
        
        $answers = array_map(
            fn($d) => new GameAnswer($d['text'], $d['points'], $d['hidden'] ?? true),
            $data
        );
        
        // Użyj count($answers) jako wartości dla $count
        $count = count($answers);
        
        // Utwórz nową instancję bezpośrednio z odpowiedziami
        $collection = new self($answers, $count);
        
        return $collection;
    }
}
