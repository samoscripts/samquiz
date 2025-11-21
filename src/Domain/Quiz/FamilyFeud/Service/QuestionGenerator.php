<?php
namespace App\Domain\Quiz\FamilyFeud\Service;

use App\Domain\Quiz\FamilyFeud\Entity\Question as DomainQuestion;
use App\Domain\Quiz\FamilyFeud\Repository\QuizRepositoryInterface;
use App\Domain\Quiz\FamilyFeud\ValueObject\Answer;
use App\Domain\Quiz\Shared\Service\AIServiceInterface;

class QuestionGenerator
{
    public function __construct(
        private AIServiceInterface $aiService,
        private PromptBuilder $promptBuilder,
        private QuizRepositoryInterface $questionRepository
    ) {}

    public function generate(string $questionText): DomainQuestion
    {
        try {
            // Sprawdzenie czy pytanie już jest w bazie danych
            $doctrineQuestion = $this->questionRepository->findByText($questionText);
            if ($doctrineQuestion !== null) {
                return $doctrineQuestion->toDomain(); // Zwracamy z bazy
            }

            // Jeśli nie ma w bazie - generujemy z ChatGPT
            $prompt = $this->promptBuilder->buildGenerateAnswersPrompt($questionText);
            $aiResponse = $this->aiService->ask($prompt);
            
            // Parsujemy odpowiedź z AI
            $answers = array_map(
                fn($answerData) => new Answer($answerData['text'], $answerData['points']),
                $aiResponse
            );
            
            // Tworzymy encję domenową
            $domainQuestion = new DomainQuestion($questionText, $answers);
            
            // Zapisujemy do bazy
            $this->questionRepository->saveDomain($domainQuestion);
            
            return $domainQuestion;
            
        } catch (\Exception $e) {
            throw new \Exception('Failed to generate question: ' . $e->getMessage());
        }
    }
}
