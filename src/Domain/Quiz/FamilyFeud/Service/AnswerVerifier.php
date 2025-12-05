<?php

namespace App\Domain\Quiz\FamilyFeud\Service;

use App\Domain\Quiz\FamilyFeud\ValueObject\PlayerAnswer as DomainPlayerAnswer;
use App\Domain\Quiz\FamilyFeud\Repository\AnswerPlayerRepositoryInterface;
use App\Domain\Quiz\FamilyFeud\Entity\Question as DomainQuestion;
use App\Domain\Quiz\Shared\Service\AIServiceInterface;
use App\Domain\Quiz\FamilyFeud\Service\PromptBuilder;
use App\Infrastructure\Persistence\Entity\Quiz\FamilyFeud\AnswerPlayer as DoctrineAnswerPlayer;
use App\Infrastructure\Persistence\Entity\Quiz\FamilyFeud\Answer as DoctrineAnswer;
use App\Infrastructure\Persistence\Entity\Quiz\FamilyFeud\Question as DoctrineQuestion;
use Doctrine\Common\Collections\Collection;
use App\Domain\Quiz\FamilyFeud\Entity\Game;
use App\Infrastructure\Persistence\Mapper\Quiz\FamilyFeud\AnswerPlayerMapper;

class AnswerVerifier
{

    public function __construct(
        private AIServiceInterface $aiService,
        private PromptBuilder $promptBuilder,
        private AnswerPlayerRepositoryInterface $answerPlayerRepository,
        private AnswerPlayerMapper $answerPlayerMapper
    ) {}
    

    public function findMatchingAnswers(
        string $answerPlayerText, 
        Game $game
    ): DomainPlayerAnswer
    {
        $doctrineAnswerPlayer = $this->answerPlayerRepository->findByPlayerTextAndQuestionId(
            $answerPlayerText,
            $game->getQuestion()->getId()
        );

        if (!$doctrineAnswerPlayer) {
            //zweryfikuj odpowiedź z AI
            $prompt = $this->promptBuilder->buildVerifyAnswerPrompt($answerPlayerText, $game->getQuestion());
            $aiResponse = $this->aiService->ask($prompt);
            $doctrineAnswerPlayer = $this->answerPlayerMapper->toEntity(
                $answerPlayerText,
                $aiResponse['found'] === true ? true : false,
                $game->getQuestion()->getId(),
                $aiResponse['answer']
            );
            $this->answerPlayerRepository->save($doctrineAnswerPlayer);
        }

        $domainAnswerPlayer = $doctrineAnswerPlayer->toDomain();
        
        
        if($doctrineAnswerPlayer->getAnswer() !== null) {
            $matchedAnswer = $game->getQuestion()->getAnswerCollection()->getByText($doctrineAnswerPlayer->getAnswer()->getText());
            // Sprawdź czy znaleziona odpowiedź jest wśród pierwszych N odpowiedzi
            
            // Jeśli nie jest w pierwszych N odpowiedziach, traktuj jako niepoprawną
            $domainAnswerPlayer = new DomainPlayerAnswer(
                playerInput: $answerPlayerText,
                matchedAnswer: $matchedAnswer,
                isCorrect: $matchedAnswer !== null ? true : false,
            );

        }
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
     * Sprawdza czy znaleziona odpowiedź jest wśród pierwszych N odpowiedzi
     * @param DoctrineAnswer $doctrineAnswer Znaleziona odpowiedź
     * @param DoctrineQuestion $doctrineQuestion Pytanie
     * @param int $answersCount Limit odpowiedzi
     * @return bool
     */
    private function isAnswerInFirstN(
        DoctrineAnswer $doctrineAnswer, 
        DomainQuestion $domainQuestion, 
        int $answersCount
    ): bool {
        // Pobierz odpowiedzi z domeny i sprawdź czy znaleziona odpowiedź jest w pierwszych N
        $limitedAnswers = $domainQuestion->getLimitedAnswers($answersCount);
        
        // Sprawdź czy tekst znalezionej odpowiedzi pasuje do którejś z pierwszych N odpowiedzi
        $answerText = $doctrineAnswer->getText();
        
        foreach ($limitedAnswers->getAnswers() as $limitedAnswer) {
            if ($this->checkAnswer($answerText, $limitedAnswer->getText())) {
                return true;
            }
        }
        
        return false;
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

