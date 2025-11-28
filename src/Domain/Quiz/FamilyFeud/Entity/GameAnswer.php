<?php

namespace App\Domain\Quiz\FamilyFeud\Entity;

use App\Domain\Quiz\FamilyFeud\ValueObject\Answer;
final class GameAnswer extends Answer
{
    public function __construct(
        readonly public string $text,
        readonly public int $points,
        private bool $hidden = true
    ) {}

    public function isHidden(): bool
    {
        return $this->hidden;
    }


}