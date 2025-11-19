<?php

namespace App\Domain\Quiz\FamilyFeud\ValueObject;

class Answer
{
    public function __construct(
        private string $text,
        private int $points
    ) {}

    public function text(): string
    {
        return $this->text;
    }

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
