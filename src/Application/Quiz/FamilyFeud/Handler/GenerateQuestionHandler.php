<?php

namespace App\Application\Quiz\FamilyFeud\Handler;

use App\Application\Quiz\FamilyFeud\Command\GenerateQuestionCommand;
use App\Domain\Quiz\FamilyFeud\Service\QuestionGenerator;

class GenerateQuestionHandler
{
    public function __construct(private readonly QuestionGenerator $generator) {}

    public function __invoke(GenerateQuestionCommand $command): array
    {
        $question = $this->generator->generate($command->question);
        return $question->toArray();
    }
}
