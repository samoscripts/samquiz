<?php

namespace App\Domain\Quiz\FamilyFeud\Service;

use App\Domain\Quiz\FamilyFeud\ValueObject\PlayerAnswer as DomainPlayerAnswer;
use App\Domain\Quiz\FamilyFeud\Repository\AnswerPlayerRepositoryInterface;
use App\Domain\Quiz\FamilyFeud\Repository\QuizRepositoryInterface;
use App\Domain\Quiz\Shared\Service\AIServiceInterface;
use App\Domain\Quiz\FamilyFeud\Service\PromptBuilder;
use App\Infrastructure\Persistence\Entity\Quiz\FamilyFeud\AnswerPlayer as DoctrineAnswerPlayer;
use App\Infrastructure\Persistence\Entity\Quiz\FamilyFeud\Answer as DoctrineAnswer;
use Doctrine\Common\Collections\Collection;

class AnswerVerifier
{

    public function __construct(
        private AIServiceInterface $aiService,
        private PromptBuilder $promptBuilder,
        private AnswerPlayerRepositoryInterface $answerPlayerRepository,
        private QuizRepositoryInterface $questionRepository,
    ) {}

    

    public function findMatchingAnswers(string $answerPlayerText, int $questionId): DomainPlayerAnswer
    {
        $doctrineAnswerPlayer = $this->answerPlayerRepository->findByPlayerTextAndQuestionId(
            $answerPlayerText,
            $questionId
        );
        
        if ($doctrineAnswerPlayer) {
            return $doctrineAnswerPlayer->toDomain();
        }

        $doctrineQuestion = $this->questionRepository->findById($questionId);
        $domainQuestion = $doctrineQuestion->toDomain();
        // Jeśli nie ma w bazie - generujemy z ChatGPT
        $prompt = $this->promptBuilder->buildVerifyAnswerPrompt($answerPlayerText, $domainQuestion);
        $aiResponse = $this->aiService->ask($prompt);

        $found = $aiResponse['found'];
        $answerText = $aiResponse['answer'];
        $doctrineAnswer = null;
        if($found === true) {
            $doctrineAnswer = $this->getAnswer($answerText, $doctrineQuestion->getAnswers());
        } 
        
        // Tworzymy encję domenową
        $domainAnswerPlayer = new DomainPlayerAnswer(
            playerInput: $answerPlayerText, 
            matchedAnswer: $doctrineAnswer ? $doctrineAnswer->toDomain() : null,
            isCorrect: $found, 
            question: $domainQuestion
        );
        $doctrineAnswerPlayer = DoctrineAnswerPlayer::fromDomain(
            $domainAnswerPlayer, 
            $doctrineQuestion, 
            $doctrineAnswer
        );

        // Zapisujemy do bazy
        $this->answerPlayerRepository->save($doctrineAnswerPlayer);
        return $domainAnswerPlayer;
    }

    /**
     * @param string $answerText
     * @param Collection<int, DoctrineAnswer> $doctrineAnswers - kolekcja odpowiedzi z bazy danych
     * @return ?DoctrineAnswer
     */
    private function getAnswer(string $answerText, Collection $doctrineAnswers): ?DoctrineAnswer
    {
        return $doctrineAnswers
            ->filter(fn(DoctrineAnswer $a) => $this->checkAnswer($answerText, $a->getText()))
            ->first() ?: null;
    }


    /**
     * Normalizuje tekst (usuwanie polskich znaków, małe litery)
     */
    private function normalizeText(string $text): string
    {
        return mb_strtolower(
            $this->removePolishChars($text)
        );
    }

    /**
     * Usuwa polskie znaki diakrytyczne
     */
    private function removePolishChars(string $text): string
    {
        $replacements = [
            'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n',
            'ó' => 'o', 'ś' => 's', 'ź' => 'z', 'ż' => 'z',
            'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'E', 'Ł' => 'L', 'Ń' => 'N',
            'Ó' => 'O', 'Ś' => 'S', 'Ź' => 'Z', 'Ż' => 'Z'
        ];

        return strtr($text, $replacements);
    }

    /**
     * Sprawdza, czy odpowiedź użytkownika pasuje do odpowiedzi z listy
     * 
     * @param string $answerPlayerText Odpowiedź wpisana przez użytkownika
     * @param string $correctAnswer Poprawna odpowiedź z listy
     * @return bool
     */
    private function checkAnswer(string $answerPlayerText, string $correctAnswer): bool
    {
        $answerPlayerText = trim($this->normalizeText($answerPlayerText));
        $correctAnswer = trim($this->normalizeText($correctAnswer));

        if (empty($answerPlayerText) || empty($correctAnswer)) {
            throw new \InvalidArgumentException('Answer player text and correct answer cannot be empty');
        }

        // Dokładne dopasowanie
        if ($answerPlayerText === $correctAnswer) {
            return true;
        }

        return false;
    }
}

