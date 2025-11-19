<?php
namespace App\Application\Quiz\FamilyFeud\Command;

class GenerateQuestionCommand
{
    public function __construct(
        public readonly string $question
    ) {}
}
