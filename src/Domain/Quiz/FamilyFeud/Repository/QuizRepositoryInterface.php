<?php

namespace App\Domain\Quiz\FamilyFeud\Repository;

use App\Domain\Quiz\FamilyFeud\Entity\Question;

interface QuizRepositoryInterface
{
    public function save(Question $question): void;

    public function findById(string $id): ?Question;

    public function findAll(): array;
}