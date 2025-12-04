<?php

namespace App\Domain\Quiz\FamilyFeud\Entity;

use App\Domain\Quiz\FamilyFeud\ValueObject\Answer;

class Question
{

    public function __construct(
        private string $text, 
        private GameAnswerCollection $answers,
        private ?int $id = null
    ) {}

    public function getId(): int
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

    public function getAnswers(): GameAnswerCollection
    {
        return $this->answers;
    }

    /**
     * Zwraca odpowiedzi ograniczone do podanej liczby
     */
    public function getLimitedAnswers(int $limit): GameAnswerCollection
    {
        return $this->answers->limit($limit);
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
        $currentSum = $limitedAnswers->totalPoints();
        
        if ($currentSum == 0) {
            return $this;
        }
        
        // Przelicz proporcjonalnie do ~100
        $targetSum = 100;
        $ratio = $targetSum / $currentSum;
        
        $recalculatedAnswers = array_map(
            fn(Answer $a) => new Answer($a->text(), (int)round($a->getPoints() * $ratio)),
            $limitedAnswers->getAnswers()
        );
        
        // Upewnij się, że suma = 100 (korekta ostatniej odpowiedzi)
        $actualSum = array_sum(array_map(fn(Answer $a) => $a->getPoints(), $recalculatedAnswers));
        if ($actualSum != 100 && count($recalculatedAnswers) > 0) {
            $lastIndex = count($recalculatedAnswers) - 1;
            $lastAnswer = $recalculatedAnswers[$lastIndex];
            $recalculatedAnswers[$lastIndex] = new Answer(
                $lastAnswer->text(),
                $lastAnswer->getPoints() + (100 - $actualSum)
            );
        }
        
        return new self($this->text, new GameAnswerCollection($recalculatedAnswers), $this->id);
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
            'answers' => $this->answers->toArray(),
        ];
    }
}
