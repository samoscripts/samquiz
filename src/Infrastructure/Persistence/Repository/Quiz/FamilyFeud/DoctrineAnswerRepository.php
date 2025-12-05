<?php

namespace App\Infrastructure\Persistence\Repository\Quiz\FamilyFeud;
use App\Domain\Quiz\FamilyFeud\Repository\AnswerRepositoryInterface;
use App\Infrastructure\Persistence\Repository\DoctrineRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Infrastructure\Persistence\Entity\Quiz\FamilyFeud\Answer as DoctrineAnswer;

class DoctrineAnswerRepository extends DoctrineRepository implements AnswerRepositoryInterface
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, DoctrineAnswer::class);
    }

    public function findOneByTextAndQuestionId(string $text, int $questionId): ?DoctrineAnswer
    {
        $respond =  $this->findOneBy(['text' => $text, 'question' => $questionId]);
        if (!$respond instanceof DoctrineAnswer) {
            return null;
        }
        return $respond;

    }
}
