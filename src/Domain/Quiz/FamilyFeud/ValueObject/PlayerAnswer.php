<?php
declare(strict_types=1);

namespace App\Domain\Quiz\FamilyFeud\ValueObject;

use App\Domain\Quiz\FamilyFeud\Entity\GameAnswer;

final class PlayerAnswer
{
    public function __construct(
        public readonly string $playerInput,
        public readonly ?GameAnswer $matchedAnswer,
        public readonly bool $isCorrect
    ) {}
    
    public static function fromPlayerInput(string $input, ?GameAnswer $answer = null): self
    {
        return new self(
            playerInput: $input,
            matchedAnswer: $answer,
            isCorrect: $answer ? true : false
        );
    }

    

    public function toArray(): array
    {
        return [
            'playerInput' => $this->playerInput,
            'matchedAnswer' => $this->matchedAnswer ? $this->matchedAnswer->toArray() : null,
            'isCorrect' => $this->isCorrect,
        ];
    }

    public function getPlayerText(): string
    {
        return $this->playerInput;
    }

    public function getMatchedAnswer(): ?GameAnswer
    {
        return $this->matchedAnswer;
    }

    public function isCorrect(): bool
    {
        return $this->isCorrect;
    }

}
