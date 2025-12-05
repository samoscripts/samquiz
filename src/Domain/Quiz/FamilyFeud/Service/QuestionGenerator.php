<?php
namespace App\Domain\Quiz\FamilyFeud\Service;

use App\Domain\Quiz\FamilyFeud\Entity\Question as DomainQuestion;
use App\Domain\Quiz\FamilyFeud\Repository\QuestionRepositoryInterface;
use App\Domain\Quiz\Shared\Service\AIServiceInterface;
use App\Domain\Quiz\FamilyFeud\Entity\GameAnswerCollection;
use App\Domain\Quiz\FamilyFeud\Entity\GameAnswer;

class QuestionGenerator
{
    public function __construct(
        private AIServiceInterface $aiService,
        private PromptBuilder $promptBuilder,
        private QuestionRepositoryInterface $questionRepository
    ) {}

    public function generate(string $questionText, int $answersCount = 10): DomainQuestion
    {
        try {
            // Sprawdzenie czy pytanie już jest w bazie danych
            $doctrineQuestion = $this->questionRepository->findByText($questionText);
            
            if ($doctrineQuestion !== null) {
                // Pobierz z bazy i przelicz punkty dla podanej liczby odpowiedzi
                $domainQuestion = $doctrineQuestion->toDomain();
                
                // Jeśli liczba odpowiedzi jest mniejsza niż dostępne, przelicz punkty
                if ($answersCount < $domainQuestion->getAnswerCollection()->count()) {
                    $domainQuestion = $domainQuestion->recalculatePointsForLimit($answersCount);
                }
                
                return $domainQuestion;
            }

            // Jeśli nie ma w bazie - generujemy z ChatGPT (zawsze 10 odpowiedzi)
            $prompt = $this->promptBuilder->buildGenerateAnswersPrompt($questionText);
            $aiResponse = $this->aiService->ask($prompt);
            
            // Parsujemy odpowiedź z AI
            $answers = array_map(
                fn($answerData) => new GameAnswer($answerData['text'], $answerData['points']),
                $aiResponse
            );
            
            $answersCollection = new GameAnswerCollection();
            $answersCollection->setAnswers($answers);
            // Tworzymy encję domenową
            $domainQuestion = new DomainQuestion($questionText, $answersCollection);
            
            // Zapisujemy do bazy
            $this->questionRepository->saveDomain($domainQuestion);
            
            // Przelicz punkty jeśli answersCount < 10
            if ($answersCount < 10 && $domainQuestion->getAnswerCollection()->count() > $answersCount) {
                $domainQuestion = $domainQuestion->recalculatePointsForLimit($answersCount);
            }
            
            return $domainQuestion;
            
        } catch (\Exception $e) {
            throw new \Exception('Failed to generate question: ' . $e->getMessage());
        }
    }
}
