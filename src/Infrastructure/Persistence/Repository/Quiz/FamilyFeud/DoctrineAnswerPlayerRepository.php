<?php

namespace App\Infrastructure\Persistence\Repository\Quiz\FamilyFeud;


use App\Infrastructure\Persistence\Entity\Quiz\FamilyFeud\AnswerPlayer as DoctrineAnswerPlayer;
use App\Domain\Quiz\FamilyFeud\Repository\AnswerPlayerRepositoryInterface;
use App\Infrastructure\Persistence\Repository\DoctrineRepository;
use Doctrine\ORM\EntityManagerInterface;



class DoctrineAnswerPlayerRepository extends DoctrineRepository implements AnswerPlayerRepositoryInterface {

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, DoctrineAnswerPlayer::class);
    }

    public function findByPlayerTextAndQuestionId(string $playerText, int $questionId): ?DoctrineAnswerPlayer
    {
        $return = $this->repository->createQueryBuilder('a')
            ->where('UPPER(a.player_text) = UPPER(:playerText)')
            ->andWhere('a.question = :questionId')
            ->setParameter('playerText', $playerText)
            ->setParameter('questionId', $questionId)
            ->getQuery()
            ->getOneOrNullResult();
        return $return;
    }

    public function save(object $entity): void
    {
        if(!$entity instanceof DoctrineAnswerPlayer) {
            throw new \InvalidArgumentException('Entity must be an instance of DoctrineAnswerPlayer');
        }
        if($entity->getIsCorrect() === true && $entity->getAnswer() === null) {
            throw new \InvalidArgumentException('Answer is required');
        }
        parent::save($entity);
    }
}