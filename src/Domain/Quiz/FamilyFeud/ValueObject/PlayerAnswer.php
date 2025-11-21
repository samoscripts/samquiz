<?php
declare(strict_types=1);

namespace App\Domain\Quiz\FamilyFeud\ValueObject;

use App\Domain\Quiz\FamilyFeud\ValueObject\Answer;
use App\Domain\Quiz\FamilyFeud\Entity\Question as DomainQuestion;

final class PlayerAnswer
{
    public function __construct(
        public readonly string $playerInput,
        public readonly ?Answer $matchedAnswer,
        public readonly bool $isCorrect,
        public readonly DomainQuestion $question
    ) {}
    
    public static function fromPlayerInput(string $input, ?Answer $answer = null, DomainQuestion $question): self
    {
        return new self(
            playerInput: $input,
            matchedAnswer: $answer,
            isCorrect: $answer ? true : false,
            question: $question
        );
    }

    

    public function toArray(): array
    {
        return [
            'playerInput' => $this->playerInput,
            'matchedAnswer' => $this->matchedAnswer ? $this->matchedAnswer->toArray() : null,
            'isCorrect' => $this->isCorrect,
            'question' => $this->question->toArray()
        ];
    }

    public function playerText(): string
    {
        return $this->playerInput;
    }

    public function matchedAnswer(): ?Answer
    {
        return $this->matchedAnswer;
    }

    public function isCorrect(): bool
    {
        return $this->isCorrect;
    }

    public function getQuestion(): DomainQuestion
    {
        return $this->question;
    }
}
