<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Shared\Repository\RepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Uniwersalna implementacja repozytorium Doctrine
 * Może być używana dla różnych typów encji
 */
abstract class DoctrineRepository implements RepositoryInterface
{
    protected EntityRepository $repository;

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected string $entityClass
    ) {
        $this->repository = $this->entityManager->getRepository($this->entityClass);
    }

    public function save(object $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function findById(int|string $id): ?object
    {
        return $this->repository->find($id);
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function remove(object $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }

    public function findBy(array $criteria): array
    {
        return $this->repository->findBy($criteria);
    }

    public function findOneBy(array $criteria): ?object
    {
        return $this->repository->findOneBy($criteria);
    }
}

