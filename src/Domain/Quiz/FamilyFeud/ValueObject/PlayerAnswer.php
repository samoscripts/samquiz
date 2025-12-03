<?php
declare(strict_types=1);

namespace App\Domain\Quiz\FamilyFeud\ValueObject;

use App\Domain\Quiz\FamilyFeud\ValueObject\Answer as DomainAnswer;

final class PlayerAnswer
{
    public function __construct(
        public readonly string $playerInput,
        public readonly ?DomainAnswer $matchedAnswer,
        public readonly bool $isCorrect
    ) {}
    
    public static function fromPlayerInput(string $input, ?DomainAnswer $answer = null): self
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

    public function playerText(): string
    {
        return $this->playerInput;
    }

    public function matchedAnswer(): ?DomainAnswer
    {
        return $this->matchedAnswer;
    }

    public function isCorrect(): bool
    {
        return $this->isCorrect;
    }

}
