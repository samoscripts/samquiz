<?php

namespace App\Domain\Quiz\FamilyFeud\Repository;

use App\Domain\Shared\Repository\RepositoryInterface;
use App\Infrastructure\Persistence\Entity\Quiz\FamilyFeud\AnswerPlayer as DoctrineAnswerPlayer;

/**
 * Specyficzny interfejs repozytorium dla Question
 * Rozszerza uniwersalny interfejs o specyficzne metody
 */
interface AnswerPlayerRepositoryInterface extends RepositoryInterface
{
    public function findByPlayerTextAndQuestionId(string $playerText, int $questionId): ?DoctrineAnswerPlayer;
}