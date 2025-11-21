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
