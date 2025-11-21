<?php

namespace App\Domain\Quiz\FamilyFeud\Repository;

use App\Domain\Shared\Repository\RepositoryInterface;
use App\Domain\Quiz\FamilyFeud\ValueObject\Answer as DomainAnswer;


interface AnswerRepositoryInterface extends RepositoryInterface
{
    public function findByTextAndQuestionId(string $text, int $questionId): ?DomainAnswer;
}