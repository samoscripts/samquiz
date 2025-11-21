<?php

namespace App\Domain\Shared\Repository;

/**
 * Uniwersalny interfejs repozytorium dla wszystkich encji
 */
interface RepositoryInterface
{
    /**
     * Zapisuje encję (tworzy lub aktualizuje)
     */
    public function save(object $entity): void;

    /**
     * Znajduje encję po ID
     */
    public function findById(int|string $id): ?object;

    /**
     * Znajduje wszystkie encje
     */
    public function findAll(): array;

    /**
     * Usuwa encję
     */
    public function remove(object $entity): void;

    /**
     * Znajduje encje według kryteriów
     * @param array<string, mixed> $criteria
     */
    public function findBy(array $criteria): array;

    /**
     * Znajduje jedną encję według kryteriów
     * @param array<string, mixed> $criteria
     */
    public function findOneBy(array $criteria): ?object;
}

