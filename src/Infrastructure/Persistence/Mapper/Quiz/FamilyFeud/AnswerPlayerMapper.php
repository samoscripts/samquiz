<?php
namespace App\Infrastructure\Persistence\Mapper\Quiz\FamilyFeud;

use App\Domain\Quiz\FamilyFeud\ValueObject\PlayerAnswer as DomainPlayerAnswer;
use App\Infrastructure\Persistence\Entity\Quiz\FamilyFeud\AnswerPlayer;
use App\Infrastructure\Persistence\Repository\Quiz\FamilyFeud\DoctrineQuestionRepository;
use App\Infrastructure\Persistence\Repository\Quiz\FamilyFeud\DoctrineAnswerRepository;

class AnswerPlayerMapper
{
    public function __construct(
        private DoctrineQuestionRepository $questionRepo,
        private DoctrineAnswerRepository $answerRepo
    ) {}

    public function toEntity(
        string $playerText,
        bool $isCorrect,
        int $questionId,
        ?string $answerText = null
    ): AnswerPlayer
    {
        $entity = new AnswerPlayer();
        $entity->setPlayerText($playerText);
        $entity->setIsCorrect($isCorrect);

        $entity->setQuestion(
            $this->questionRepo->findById($questionId)
        );

        if ($answerText !== null) {
            $answer = $this->answerRepo->findOneByTextAndQuestionId($answerText, $questionId);
            if ($answer) {
                $entity->setAnswer($answer);
            }
        }

        return $entity;
    }

    public function toDomain(AnswerPlayer $entity): DomainPlayerAnswer
    {
        return new DomainPlayerAnswer(
            $entity->getPlayerText(),
            $entity->getAnswer()?->toDomain(),
            $entity->getIsCorrect(),
            $entity->getQuestion()->getId()
        );
    }
}
