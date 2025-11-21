<?php

namespace App\Infrastructure\Persistence\Repository\Quiz\FamilyFeud;

use App\Domain\Quiz\FamilyFeud\Entity\Question as DomainQuestion;
use App\Domain\Quiz\FamilyFeud\Repository\QuizRepositoryInterface;
use App\Domain\Quiz\FamilyFeud\ValueObject\Answer as DomainAnswer;
use App\Infrastructure\Persistence\Entity\Quiz\FamilyFeud\Question as DoctrineQuestion;
use App\Infrastructure\Persistence\Entity\Quiz\FamilyFeud\Answer as DoctrineAnswer;
use App\Infrastructure\Persistence\Entity\Quiz\FamilyFeud\AnswerPlayer as DoctrineAnswerPlayer;
use App\Infrastructure\Persistence\Repository\DoctrineRepository;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineQuizRepository extends DoctrineRepository implements QuizRepositoryInterface
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, DoctrineQuestion::class);
    }

    /**
     * Implementacja uniwersalnej metody save z mapowaniem Domain -> Doctrine
     */
    public function save(object $entity): void
    {
        if (!$entity instanceof DomainQuestion) {
            throw new \InvalidArgumentException('Expected DomainQuestion instance');
        }

        $this->saveDomain($entity);
    }

    /**
     * Specyficzna metoda dla Question - zapisz z mapowaniem Domain -> Doctrine
     */
    public function saveDomain(DomainQuestion $question): void
    {
        // Sprawdzamy czy pytanie już istnieje
        $existing = $this->findByText($question->text());
        if ($existing) {
            $question->setId($existing->getId());
            return; // Już istnieje, nie zapisujemy ponownie
        }

        // Tworzymy Doctrine Entity
        $doctrineQuestion = new DoctrineQuestion();
        $doctrineQuestion->setText($question->text());
        $doctrineQuestion->setCreatedAt(new \DateTime());

        // Tworzymy odpowiedzi
        foreach ($question->answers() as $domainAnswer) {
            $doctrineAnswer = new DoctrineAnswer();
            $doctrineAnswer->setText($domainAnswer->text());
            $doctrineAnswer->setPoints($domainAnswer->points());
            $doctrineAnswer->setQuestion($doctrineQuestion);
            $doctrineAnswer->setCreatedAt(new \DateTime());

            $doctrineAnswerPlayer = new DoctrineAnswerPlayer();
            $doctrineAnswerPlayer->setPlayerText($domainAnswer->text());
            $doctrineAnswerPlayer->setIsCorrect(true);
            $doctrineAnswerPlayer->setQuestion($doctrineQuestion);
            $doctrineAnswerPlayer->setAnswer($doctrineAnswer);
            $doctrineAnswerPlayer->setCreatedAt(new \DateTime());

            $doctrineQuestion->addAnswer($doctrineAnswer);
            $this->entityManager->persist($doctrineAnswer);
            $this->entityManager->persist($doctrineAnswerPlayer);
        }

        $this->entityManager->persist($doctrineQuestion);
        $this->entityManager->flush();
        $question->setId($doctrineQuestion->getId());
    }

    /**
     * Implementacja uniwersalnej metody findById z mapowaniem Doctrine -> Domain
     */
    public function findById(int|string $id): ?DoctrineQuestion
    {
        $doctrineQuestion = parent::findById($id);
        
        if (!$doctrineQuestion instanceof DoctrineQuestion) {
            return null;
        }
        return $doctrineQuestion;
    }

    public function findByText(string $text): ?DoctrineQuestion
    {
        $doctrineQuestion = $this->findOneBy(['text' => $text]);

        if (!$doctrineQuestion instanceof DoctrineQuestion) {
            return null;
        }
        return $doctrineQuestion;

    }

    /**
     * Implementacja uniwersalnej metody findAll z mapowaniem Doctrine -> Domain
     */
    public function findAll(): array
    {
        $doctrineQuestions = parent::findAll();
        
        return array_map(
            fn($dq) => $this->mapToDomain($dq),
            $doctrineQuestions
        );
    }

    /**
     * Implementacja uniwersalnej metody remove
     */
    public function remove(object $entity): void
    {
        if (!$entity instanceof DoctrineQuestion) {
            throw new \InvalidArgumentException('Expected DoctrineQuestion instance');
        }

        parent::remove($entity);
    }

    /**
     * Implementacja uniwersalnej metody findBy z mapowaniem Doctrine -> Domain
     */
    public function findBy(array $criteria): array
    {
        $doctrineQuestions = parent::findBy($criteria);
        
        return array_map(
            fn($dq) => $this->mapToDomain($dq),
            $doctrineQuestions
        );
    }

    /**
     * Implementacja uniwersalnej metody findOneBy z mapowaniem Doctrine -> Domain
     */
    public function findOneBy(array $criteria): ?DomainQuestion
    {
        $doctrineQuestion = parent::findOneBy($criteria);
        
        if (!$doctrineQuestion instanceof DoctrineQuestion) {
            return null;
        }

        return $this->mapToDomain($doctrineQuestion);
    }

    /**
     * Mapuje Doctrine Entity na Domain Entity
     */
    public function mapToDomain(DoctrineQuestion $doctrineQuestion): DomainQuestion
    {
        $domainAnswers = [];
        
        foreach ($doctrineQuestion->getAnswers() as $doctrineAnswer) {
            $domainAnswers[] = new DomainAnswer(
                $doctrineAnswer->getText(),
                $doctrineAnswer->getPoints()
            );
        }

        return new DomainQuestion(
            $doctrineQuestion->getText(), 
            $domainAnswers, 
            $doctrineQuestion->getId()
        );
    }
}

