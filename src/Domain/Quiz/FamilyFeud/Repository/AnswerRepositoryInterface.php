<?php

namespace App\Domain\Quiz\FamilyFeud\Repository;

use App\Domain\Shared\Repository\RepositoryInterface;
use App\Infrastructure\Persistence\Entity\Quiz\FamilyFeud\Answer as DoctrineAnswer;


interface AnswerRepositoryInterface extends RepositoryInterface
{
    public function findOneByTextAndQuestionId(string $text, int $questionId): ?DoctrineAnswer;
}