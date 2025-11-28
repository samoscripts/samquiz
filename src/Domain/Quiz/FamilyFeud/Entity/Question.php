<?php

namespace App\Domain\Quiz\FamilyFeud\Entity;

use App\Domain\Quiz\FamilyFeud\ValueObject\Answer;

class Question
{

    /**
     * @param Answer[] $answers
     */
    public function __construct(
        private string $text, 
        private array $answers,
        private ?int $id = null
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function text(): string
    {
        return $this->text;
    }

    /** @return Answer[] */
    public function answers(): array
    {
        return $this->answers;
    }

    /**
     * Zwraca odpowiedzi ograniczone do podanej liczby
     * @return Answer[]
     */
    public function getLimitedAnswers(int $limit): array
    {
        return array_slice($this->answers, 0, min($limit, count($this->answers)));
    }

    /**
     * Przelicza punkty dla ograniczonej liczby odpowiedzi, tak aby suma wynosiła ~100
     * @return self Nowa instancja z przeliczonymi punktami
     */
    public function recalculatePointsForLimit(int $limit): self
    {
        $limitedAnswers = $this->getLimitedAnswers($limit);
        
        if (empty($limitedAnswers)) {
            return $this;
        }
        
        // Oblicz sumę punktów z ograniczonych odpowiedzi
        $currentSum = array_sum(array_map(fn(Answer $a) => $a->points(), $limitedAnswers));
        
        if ($currentSum == 0) {
            return $this;
        }
        
        // Przelicz proporcjonalnie do ~100
        $targetSum = 100;
        $ratio = $targetSum / $currentSum;
        
        $recalculatedAnswers = array_map(
            fn(Answer $a) => new Answer($a->text(), (int)round($a->points() * $ratio)),
            $limitedAnswers
        );
        
        // Upewnij się, że suma = 100 (korekta ostatniej odpowiedzi)
        $actualSum = array_sum(array_map(fn(Answer $a) => $a->points(), $recalculatedAnswers));
        if ($actualSum != 100 && count($recalculatedAnswers) > 0) {
            $lastIndex = count($recalculatedAnswers) - 1;
            $lastAnswer = $recalculatedAnswers[$lastIndex];
            $recalculatedAnswers[$lastIndex] = new Answer(
                $lastAnswer->text(),
                $lastAnswer->points() + (100 - $actualSum)
            );
        }
        
        return new self($this->text, $recalculatedAnswers, $this->id);
    }

    public function toArray(): array
    {
        if ($this->id === null) {
            throw new \Exception('Question ID is not set');
        }
        if ($this->text === null) {
            throw new \Exception('Question text is not set');
        }
        if ($this->answers === null) {
            throw new \Exception('Question answers are not set');
        }
        return [
            'id' => $this->id,
            'question' => $this->text,
            'answers' => array_map(fn(Answer $a) => $a->toArray(), $this->answers),
        ];
    }
}
