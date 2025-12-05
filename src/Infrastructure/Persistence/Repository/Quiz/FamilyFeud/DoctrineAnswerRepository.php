<?php

namespace App\Infrastructure\Persistence\Repository\Quiz\FamilyFeud;
use App\Domain\Quiz\FamilyFeud\Repository\AnswerRepositoryInterface;
use App\Infrastructure\Persistence\Repository\DoctrineRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Infrastructure\Persistence\Entity\Quiz\FamilyFeud\Answer as DoctrineAnswer;
use App\Domain\Quiz\FamilyFeud\Entity\GameAnswer as DomainAnswer;

class DoctrineAnswerRepository extends DoctrineRepository implements AnswerRepositoryInterface
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, DoctrineAnswer::class);
    }

    public function findByTextAndQuestionId(string $text, int $questionId): ?DomainAnswer
    {
        $respond =  $this->findOneBy(['text' => $text, 'question' => $questionId]);
        if (!$respond instanceof DoctrineAnswer) {
            return null;
        }
        return new DomainAnswer($respond->getText(), $respond->getPoints(), $respond->getId());

    }
}
