<?php

namespace App\Domain\Quiz\FamilyFeud\Repository;

use App\Domain\Shared\Repository\RepositoryInterface;
use App\Domain\Quiz\FamilyFeud\Entity\GameAnswer as DomainAnswer;


interface AnswerRepositoryInterface extends RepositoryInterface
{
    public function findByTextAndQuestionId(string $text, int $questionId): ?DomainAnswer;
}