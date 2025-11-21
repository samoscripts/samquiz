<?php

namespace App\Infrastructure\Persistence\Repository\Quiz\FamilyFeud;


use App\Infrastructure\Persistence\Entity\Quiz\FamilyFeud\AnswerPlayer as DoctrineAnswerPlayer;
use App\Domain\Quiz\FamilyFeud\ValueObject\PlayerAnswer as DomainPlayerAnswer;
use App\Domain\Quiz\FamilyFeud\Repository\AnswerPlayerRepositoryInterface;
use App\Infrastructure\Persistence\Repository\DoctrineRepository;
use App\Infrastructure\Persistence\Entity\Quiz\FamilyFeud\Answer as DoctrineAnswer;
use App\Infrastructure\Persistence\Entity\Quiz\FamilyFeud\Question as DoctrineQuestion;
use Doctrine\ORM\EntityManagerInterface;



class DoctrineAnswerPlayerRepository extends DoctrineRepository implements AnswerPlayerRepositoryInterface {

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, DoctrineAnswerPlayer::class);
    }

    public function findByPlayerTextAndQuestionId(string $playerText, int $questionId): ?DoctrineAnswerPlayer
    {
        return $this->findOneBy(['player_text' => $playerText, 'question' => $questionId]);
    }
}