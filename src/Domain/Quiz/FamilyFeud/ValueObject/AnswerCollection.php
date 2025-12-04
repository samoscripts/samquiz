<?php

namespace App\Domain\Quiz\FamilyFeud\ValueObject;

use App\Domain\Quiz\FamilyFeud\ValueObject\Answer;

/**
 * Kolekcja odpowiedzi z metodami pomocniczymi
 */
class AnswerCollection implements \IteratorAggregate, \Countable
{
    /** @var Answer[] */
    private array $items = [];

    /**
     * @param Answer[] $answers
     */
    public function __construct(array $answers = [])
    {
        $this->validateAnswers($answers);
        $this->items = $answers;
    }

    /**
     * Walidacja, że wszystkie elementy są instancjami Answer
     * @param array $answers
     */
    private function validateAnswers(array $answers): void
    {
        foreach ($answers as $answer) {
            if (!$answer instanceof Answer) {
                throw new \InvalidArgumentException('All elements must be instances of Answer');
            }
        }
    }

    /**
     * Dodaje odpowiedź do kolekcji
     */
    public function add(Answer $answer): void
    {
        $this->items[] = $answer;
    }

    /**
     * Usuwa odpowiedź z kolekcji
     */
    public function remove(Answer $answer): void
    {
        $this->items = array_filter(
            $this->items,
            fn(Answer $a) => $a !== $answer
        );
        $this->items = array_values($this->items); // Reindex array
    }

    /**
     * Pobiera odpowiedź po indeksie
     */
    public function get(int $index): ?Answer
    {
        return $this->items[$index] ?? null;
    }

    /**
     * Pobiera odpowiedź po tekście
     */
    public function getByText(string $text): ?Answer
    {
        foreach ($this->items as $answer) {
            if ($answer->text() === $text) {
                return $answer;
            }
        }
        return null;
    }

    /**
     * Sprawdza czy odpowiedź istnieje w kolekcji
     */
    public function contains(Answer $answer): bool
    {
        return in_array($answer, $this->items, true);
    }

    /**
     * Sprawdza czy kolekcja jest pusta
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Zwraca pierwszą odpowiedź
     */
    public function first(): ?Answer
    {
        return $this->items[0] ?? null;
    }

    /**
     * Zwraca ostatnią odpowiedź
     */
    public function last(): ?Answer
    {
        if (empty($this->items)) {
            return null;
        }
        return $this->items[count($this->items) - 1];
    }

    /**
     * Zwraca wszystkie odpowiedzi jako tablicę
     * @return Answer[]
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Zwraca nową kolekcję z ograniczoną liczbą odpowiedzi
     */
    public function limit(int $count): self
    {
        return new self(array_slice($this->items, 0, $count));
    }

    /**
     * Zwraca nową kolekcję z wyciętym fragmentem
     */
    public function slice(int $offset, ?int $length = null): self
    {
        return new self(array_slice($this->items, $offset, $length));
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Sumuje punkty wszystkich odpowiedzi
     */
    public function totalPoints(): int
    {
        return array_sum(
            array_map(fn(Answer $a) => $a->getPoints(), $this->items)
        );
    }

    /**
     * Zwraca nową kolekcję z przeliczeniem punktów tak,
     * aby suma wynosiła 100 (proporcjonalnie).
     * 
     * @throws \RuntimeException Jeśli suma punktów wynosi 0 lub kolekcja jest pusta
     */
    public function normalizeTo100(): self
    {
        if ($this->isEmpty()) {
            throw new \RuntimeException('Cannot normalize empty collection');
        }

        $total = $this->totalPoints();

        if ($total === 0) {
            throw new \RuntimeException('Cannot normalize collection with zero total points');
        }

        $normalized = [];

        foreach ($this->items as $answer) {
            $percent = ($answer->getPoints() / $total) * 100;
            $points = (int) round($percent);
            $normalized[] = new Answer($answer->text(), $points);
        }

        // Korekta sumy, aby wynosiła dokładnie 100
        $sum = array_sum(array_map(fn(Answer $a) => $a->getPoints(), $normalized));
        $diff = 100 - $sum;

        if ($diff !== 0 && count($normalized) > 0) {
            // Dodaj brakujący punkt/punkty do ostatniego elementu (bardziej naturalne)
            $last = $normalized[count($normalized) - 1];
            $normalized[count($normalized) - 1] = new Answer($last->text(), $last->getPoints() + $diff);
        }

        return new self($normalized);
    }

    public function toArray(): array
    {
        return array_map(fn(Answer $a) => $a->toArray(), $this->items);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            array_map(
                fn($d) => new Answer($d['text'], $d['points']),
                $data
            )
        );
    }
}
