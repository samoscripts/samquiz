<?php
declare(strict_types=1);

namespace App\Domain\Quiz\FamilyFeud\ValueObject;

use App\Domain\Quiz\FamilyFeud\ValueObject\Answer;

final class PlayerAnswer
{
    public function __construct(
        public readonly string $playerInput,
        public readonly ?Answer $matchedAnswer,
        public readonly bool $isCorrect
    ) {}
    
    public static function fromPlayerInput(string $input, ?Answer $answer = null): self
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
            'isCorrect' => $this->isCorrect
        ];
    }
}
