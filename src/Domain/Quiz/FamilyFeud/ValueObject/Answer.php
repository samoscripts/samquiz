<?php

namespace App\Domain\Quiz\FamilyFeud\ValueObject;

class Answer
{
    public function __construct(
        readonly public string $text,
        readonly public int $points
    ) {}

    /**
     * @deprecated Use $answer->text directly
     */
    public function text(): string
    {
        return $this->text;
    }

    /**
     * @deprecated Use $answer->points directly
     */
    public function points(): int
    {
        return $this->points;
    }

    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'points' => $this->points,
        ];
    }
}
