<?php

namespace App\Domain\Quiz\FamilyFeud\ValueObject;

class Answer
{
    public function __construct(
        readonly public string $text,
        readonly public int $points,
        readonly public ?int $id = null
    ) {}

    /**
     * @deprecated Use $answer->text directly
     */
    public function text(): string
    {
        return $this->text;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'points' => $this->points,
            'id' => $this->id,
        ];
    }
}
