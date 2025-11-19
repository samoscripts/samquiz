<?php

namespace App\Domain\Quiz\FamilyFeud\Entity;

use App\Domain\Quiz\FamilyFeud\ValueObject\Answer;

class Question
{
    /** @var Answer[] */
    private array $answers;

    public function __construct(private string $text, array $answers)
    {
        $this->answers = $answers;
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
        return [
            'question' => $this->text,
            'answers' => array_map(fn(Answer $a) => $a->toArray(), $this->answers),
        ];
    }
}
