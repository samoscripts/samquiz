<?php

namespace App\Domain\Quiz\FamilyFeud\Entity;

use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
class Question
{
    

    public function __construct(
        #[Groups(['public'])]
        private string $text,
        #[Groups(['public'])]
        #[SerializedName('answerCollection')]
        private GameAnswerCollection $answerCollection,
        #[Groups(['public'])]
        private ?int $id = null,
        #[Groups(['public'])]
        #[SerializedName('revealedAnswers')]
        private ?GameAnswerCollection $revealedAnswers = new GameAnswerCollection()
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

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    public function getAnswerCollection(): GameAnswerCollection
    {
        return $this->answerCollection;
    }

    public function setAnswerCollection(GameAnswerCollection $answerCollection): void
    {
        $this->answerCollection = $answerCollection;
    }
    /**
     * Zwraca odpowiedzi ograniczone do podanej liczby
     */
    public function getLimitedAnswers(int $limit): GameAnswerCollection
    {
        return $this->answerCollection->limit($limit);
    }

    public function getRevealedAnswers(): GameAnswerCollection
    {
        return $this->revealedAnswers;
    }

    public function setRevealedAnswers(GameAnswerCollection $revealedAnswers): void
    {
        $this->revealedAnswers = $revealedAnswers;
    }

    public function flushRevealedAnswers(): void
    {
        $this->revealedAnswers = new GameAnswerCollection();
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
            fn(GameAnswer $a) => new GameAnswer($a->text, (int)round($a->points * $ratio), $a->id),
            $limitedAnswers->getAnswers()
        );
        
        // Upewnij się, że suma = 100 (korekta ostatniej odpowiedzi)
        $actualSum = array_sum(array_map(fn(GameAnswer $a) => $a->points, $recalculatedAnswers));
        if ($actualSum != 100 && count($recalculatedAnswers) > 0) {
            $lastIndex = count($recalculatedAnswers) - 1;
            $lastAnswer = $recalculatedAnswers[$lastIndex];
            $recalculatedAnswers[$lastIndex] = new GameAnswer(
                $lastAnswer->text,
                $lastAnswer->points + (100 - $actualSum),
                $lastAnswer->id
            );
        }

        $answersCollection = new GameAnswerCollection();
        $answersCollection->setAnswers($recalculatedAnswers);
        return new self($this->text, $answersCollection, $this->id);
    }

    public function toArray(): array
    {
        if ($this->id === null) {
            throw new \Exception('Question ID is not set');
        }
        if ($this->text === null) {
            throw new \Exception('Question text is not set');
        }
        if ($this->answerCollection === null) {
            throw new \Exception('Question answers are not set');
        }
        return [
            'id' => $this->id,
            'text' => $this->text,
            'answerCollection' => $this->answerCollection->toArray(),
        ];
    }
}
