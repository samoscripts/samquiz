<?php

namespace App\Domain\Quiz\FamilyFeud\Repository;

use App\Infrastructure\Persistence\Entity\Quiz\FamilyFeud\Question as DoctrineQuestion;
use App\Domain\Shared\Repository\RepositoryInterface;
use App\Domain\Quiz\FamilyFeud\Entity\Question as DomainQuestion;
/**
 * Specyficzny interfejs repozytorium dla Question
 * Rozszerza uniwersalny interfejs o specyficzne metody
 */
interface QuestionRepositoryInterface extends RepositoryInterface
{
    /**
     * Specyficzna metoda dla Question - znajdź po tekście
     */
    public function findByText(string $text): ?DoctrineQuestion;

    public function saveDomain(DomainQuestion $question): void;
}